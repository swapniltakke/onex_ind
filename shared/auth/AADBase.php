<?php

Class AADBase{
    protected string $baseURL;
    protected string $CLIENT_ID;
    protected string $CLIENT_SECRET_VALUE;
    protected string $TENANT_ID;
    protected string $SCOPE;
    protected string $ONEX_TOKEN_SECRET;
    protected string $ACCESS_TOKEN_NAME;
    protected string $REFRESH_TOKEN_NAME;
    public array $headersCustom = array('alg' => 'HS512', 'typ' => 'JWT');
    protected object|null $AADTokenPayload;
    protected string $onexRedirectURL;
    protected array $expectedOnexURLS;
    protected string $userGID;
    protected int $ONEX_ACCESS_TOKEN_EXPIRE_DURATION;
    protected int $ONEX_REFRESH_TOKEN_EXPIRE_DURATION;

    public function initEnv(){
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/composer/vendor/autoload.php";

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $this->ACCESS_TOKEN_NAME = $_ENV['ACCESS_TOKEN_NAME'];
        $this->REFRESH_TOKEN_NAME = $_ENV['REFRESH_TOKEN_NAME'];
        $this->ONEX_TOKEN_SECRET = $_ENV['ONEX_TOKEN_SECRET'];
        $this->ONEX_ACCESS_TOKEN_EXPIRE_DURATION = (int) $_ENV['ONEX_ACCESS_TOKEN_EXPIRE_DURATION'];
        $this->ONEX_REFRESH_TOKEN_EXPIRE_DURATION = (int) $_ENV['ONEX_REFRESH_TOKEN_EXPIRE_DURATION'];

        $this->CLIENT_ID = $_ENV['CLIENT_ID'];
        $this->CLIENT_SECRET_VALUE = $_ENV['CLIENT_SECRET_VALUE'];
        $this->TENANT_ID = $_ENV['TENANT_ID'];
        $this->SCOPE = $_ENV['SCOPE'];

        $this->expectedOnexURLS = explode(',', $_ENV['EXPECTED_ONEX_URLS']);
    }

    private function CURL_GET($dataArray, $url){
        $ch = curl_init();
        if ($dataArray != null) {
            $data = http_build_query($dataArray);
            $getUrl = $url . "?" . $data;
        } else {
            $getUrl = $url;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        $response = curl_exec($ch);

        if (curl_error($ch)) {
            echo 'Request Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    private function CURL_POST($dataArray, $url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($dataArray));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $url);
        $response = curl_exec($ch);
        if (curl_error($ch)) {
            echo 'Request Error:' . curl_error($ch);
        }
        curl_close($ch);
        return $response;
    }

    private function getAccessTokenSignatures(){
        $signutaresURL = "$this->baseURL/discovery/keys";
        $signatures = $this->CURL_GET(["appid" => $this->CLIENT_ID], $signutaresURL);
        return json_decode($signatures)->keys;
    }

    public function getAADRedirectURL(){
        $this->initEnv();
        $this->baseURL = "https://login.microsoftonline.com/$this->TENANT_ID";
        $REDIRECT_URI = $this->getCurrentUrl();
        $REDIRECT_URI_ENCODED = rawurlencode($REDIRECT_URI);
        $ONEX_REDIRECT_URI = $_GET['redirect'] ?? $this->getCurrentUrl();
        $ONEX_REDIRECT_URI_ENCODED = rawurlencode($ONEX_REDIRECT_URI);

        return "$this->baseURL/oauth2/v2.0/authorize?response_type=code&state=$ONEX_REDIRECT_URI_ENCODED&client_id=$this->CLIENT_ID&scope=$this->SCOPE&redirect_uri=$REDIRECT_URI_ENCODED";
    }

    protected function valideStateParameter($state, $_CODE){
        $state = rawurldecode($state);
        $isValidURL = filter_var($state, FILTER_VALIDATE_URL);
        $isExpectedURL = $this->getIsExpectedURL($state);
        if(!$isValidURL || !$isExpectedURL){
            $mailBody = "state parameter does not contain valid/expected OneX URL<br>Code: $_CODE<br>State URL: $state";
            $this->sendNotificationEmail('[IMPORTANT] - OneX AzureAD Injection Attempt', $mailBody, 400, "Recieved state parameter in authorization code from AzureAD ($state) is not verified. CODE: $_CODE");
        }
    }

    protected function getIsExpectedURL($state){
        foreach ($this->expectedOnexURLS as $url){
            if(str_starts_with($state, $url))
                return true;
        }
        return false;
    }

    protected function getAADTokens($_CODE){
        $tokenRequestURL = "$this->baseURL/oauth2/v2.0/token";

        $dataArray = [
            'client_id' => $this->CLIENT_ID,
            'client_secret' => $this->CLIENT_SECRET_VALUE,
            'grant_type' => 'authorization_code',
            'code' => $_CODE,
            'state' => $this->onexRedirectURL,
            'redirect_uri' => $this->getHttpHostURL(),
            'scope' => $this->SCOPE
        ];
        $response = $this->CURL_POST($dataArray, $tokenRequestURL);
        $responseJSON = json_decode($response, true);

        $errorMsg = $responseJSON["error"];
        if ($errorMsg)
            returnHttpResponse(500, $errorMsg . PHP_EOL . $responseJSON["error_description"]);

        return $responseJSON;
    }

    private function sendNotificationEmail(string $subject, string $body, int $code, string $message){
        $USER_IP = SharedManager::getUserIP();
        $currentURL = $this->getCurrentUrl();
        $body .= "<br><br>URL: $currentURL<br>User IP: $USER_IP";
        MailManager::sendMail($subject, $body, 7, 'internal');
        returnHttpResponse($code, $message);
    }

    protected function verifyAADAccessToken($AAD_ACCESS_TOKEN){
        $accessTokenHeader = $this->getTokenPart($AAD_ACCESS_TOKEN, 0);
        $accessTokenKID = $accessTokenHeader->kid;

        $isAccessTokenVerified = false;
        foreach ($this->getAccessTokenSignatures() as $signature){
            $signatureKID = $signature->kid;
            if($accessTokenKID === $signatureKID)
                $isAccessTokenVerified = true;
        }
        if(!$isAccessTokenVerified){
            $tokenPayload = base64_decode(explode('.', $AAD_ACCESS_TOKEN)[1]);
            $mailBody = "AzureAD Token: $AAD_ACCESS_TOKEN<br><br>Payload: $tokenPayload";
            $this->sendNotificationEmail('[ÖNEMLİ] - OneX AzureAD Injection', $mailBody, 400, "Access token($AAD_ACCESS_TOKEN) is not verified");
        }

        return true;
    }

    public function getTokenPart(string $token, int $partNumber, $decode = true): object|false|string
    {
        $tokenParts = explode('.', $token);
        $payload = base64_decode($tokenParts[$partNumber]);
        return ($decode) ? json_decode($payload) : $payload;
    }

    protected function is_jwt_signature_valid($token, $algo, $secret): bool
    {
        $tokenParts = explode('.', $token);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        $base64_url_header = $this->base64url_encode($header);
        $base64_url_payload = $this->base64url_encode($payload);
        $signature = hash_hmac($algo, $base64_url_header . "." . $base64_url_payload, $secret, true);
        $base64_url_signature = $this->base64url_encode($signature);
        return $base64_url_signature === $signature_provided;
    }

    function base64url_encode($data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function is_jwt_valid($token): bool
    {
        $tokenParts = explode('.', $token);
        $header = base64_decode($tokenParts[0]);
        $payload = base64_decode($tokenParts[1]);
        $signature_provided = $tokenParts[2];

        $expiration = json_decode($payload)->exp;
        $is_token_expired = ($expiration - time()) < 0;

        $base64_url_header = $this->base64url_encode($header);
        $base64_url_payload = $this->base64url_encode($payload);
        $signature = hash_hmac('SHA512', $base64_url_header . "." . $base64_url_payload, $this->ONEX_TOKEN_SECRET, true);
        $base64_url_signature = $this->base64url_encode($signature);

        $is_signature_valid = ($base64_url_signature === $signature_provided);

        return !($is_token_expired || !$is_signature_valid);
    }

    public function refreshConditions($onexAccessToken = null)
    {
        $oldAccessTokenPayload = (object) ($onexAccessToken ?? $this->getTokenPart($_COOKIE[$this->getAccessTokenName()], 1));
        $refreshTokenPayload = $this->getTokenPart($_COOKIE[$this->getRefreshTokenName()], 1);
        $newTokenPayload = $this->getAccessTokenPayload($oldAccessTokenPayload);
        $newAccessToken = $this->generate_jwt($this->headersCustom, $newTokenPayload);
        $this->setSecureCookie($this->getAccessTokenName(), $newAccessToken, $refreshTokenPayload->exp);

        return $newAccessToken;
    }

    public function getAccessTokenPayload(object $tokenPayload)
    {
        $userPermissions = $this->getUserPermissions($tokenPayload);
        preg_match('/\((.*?)\)/', $tokenPayload->name, $matches);
        $org_code = $matches[1] ?? $tokenPayload->org_code;

        return [
            'iat' => time(),
            'exp' => time() + $this->ONEX_ACCESS_TOKEN_EXPIRE_DURATION,
            'gid' => $userPermissions->gid,
            'mail' => $tokenPayload->unique_name ?? $tokenPayload->mail,
            'name' => $tokenPayload->given_name ?? $tokenPayload->name,
            'surname' => $tokenPayload->family_name ?? $tokenPayload->surname,
            'org_code' => $org_code,
            //"modules" => $userPermissions->modules,
            "modulesStr" => $userPermissions->modulesStr,
            //"functions" => $userPermissions->functions,
            "functionsStr" => $userPermissions->functionsStr,
            "group_id" => $userPermissions->group_id,
            "oid" => $tokenPayload->oid
        ];
    }

    private function getUserInfo($tokenPayload)
    {
        $userPermissionQuery = "
            SELECT 
                modules, 
                modules AS modulesStr, 
                functions, 
                functions AS functionsStr, 
                group_id, 
                gid,
                current_org_code 
            FROM 
                users 
            WHERE 
                email = :email AND 
                status = '1' 
            LIMIT 1
        ";

        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/PDOManager.php";
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/DbManager.php";
        $email = $tokenPayload->unique_name ?? $tokenPayload->mail;
        $userPermissions = DbManager::fetchPDOQueryData('php_auth', $userPermissionQuery, [':email' => $email])["data"][0];
        if (!$userPermissions) {
            $userName = $tokenPayload->given_name;
            $userSurname = $tokenPayload->family_name;
            $REDIRECT_PATH = $this->getCurrentUrl();

            $this->redirect("/shared/screens/notallowed.php?email=$email&name=$userName&surname=$userSurname&link=$REDIRECT_PATH");
            exit;
        }

        $this->userGID = $userPermissions["gid"];

        return $userPermissions;
    }

    private function getUserPermissions($tokenPayload)
    {
        $email = $tokenPayload->unique_name ?? $tokenPayload->mail;
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $decodedTokenPayload = json_encode((array)$tokenPayload);
            MailManager::sendMail('[AADAuth]-Not a valid email', "<b>$email</b> is not a valid email, token payload: " . $decodedTokenPayload, 7, 'internal');
            throw new InvalidArgumentException("$email is not a valid email, token payload: " . $decodedTokenPayload);
        }

        $userPermissions = $this->getUserInfo($tokenPayload);
        $userPermissions["modules"] = array_filter(array_map('intval', explode('|', $userPermissions["modules"])), 'is_int');
        $userPermissions["functions"] = array_filter(array_map('intval', explode('|', $userPermissions["functions"])), 'is_int');
        $userPermissions["group_id"] = intval($userPermissions["group_id"]);

        return (object)$userPermissions;
    }

    public function generate_jwt($headers, $payload): string
    {
        $headers_encoded = $this->base64url_encode(json_encode($headers));
        $payload_encoded = $this->base64url_encode(json_encode($payload));

        $signature = hash_hmac('SHA512', "$headers_encoded.$payload_encoded", $this->ONEX_TOKEN_SECRET, true);
        $signature_encoded = $this->base64url_encode($signature);

        return "$headers_encoded.$payload_encoded.$signature_encoded";
    }

    public function setSecureCookie($name, $value, $expireTime)
    {
        $host = str_contains($_SERVER['HTTP_HOST'], 'localhost') ? 'localhost' : $_SERVER['HTTP_HOST'];
        $path = '/';
        setcookie($name, $value, $expireTime, $path, $host, true, true);
    }

    public function deleteSecureCookie($name, $value)
    {
        $host = str_contains($_SERVER['HTTP_HOST'], 'localhost') ? 'localhost' : $_SERVER['HTTP_HOST'];
        $expire = time() - 3600;
        $path = '/';
        setcookie($name, $value, $expire, $path, $host, true, true);
    }

    protected function noLogin()
    {
        $actual_link = $this->getCurrentUrl();
        $sso_link = rawurlencode($actual_link);
        header("Location: /shared/screens/nologin.php?redirect=" . rawurlencode($sso_link));
        die();
    }

    public function getHttpHostURL(){
        $HTTP = ($_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $HOST = $_SERVER['HTTP_HOST'];
        return "$HTTP://$HOST";
    }

    public function getCurrentUrl()
    {
        $httpHost = $this->getHttpHostURL();
        $URI = $_SERVER['REQUEST_URI'];
        return (str_contains(strtolower($URI), "/shared/screens/nologin.php")) ? $httpHost : "$httpHost$URI";
    }

    protected function generateOnexTokens()
    {
        $newAccessTokenPayload = $this->getAccessTokenPayload($this->AADTokenPayload);
        $accessToken = $this->generate_jwt($this->headersCustom, $newAccessTokenPayload);
        $refreshToken = $this->generate_jwt($this->headersCustom, [
            'iat' => time(),
            'exp' => time() + $this->ONEX_REFRESH_TOKEN_EXPIRE_DURATION,
            'gid' => $newAccessTokenPayload["gid"]
        ]);
        $this->setSecureCookie($this->ACCESS_TOKEN_NAME, $accessToken, time() + $this->ONEX_REFRESH_TOKEN_EXPIRE_DURATION);
        $this->setSecureCookie($this->REFRESH_TOKEN_NAME, $refreshToken, time() + $this->ONEX_REFRESH_TOKEN_EXPIRE_DURATION);

        return true;
    }

    protected function saveUserImage($AAD_ACCESS_TOKEN){
        try{
            $oid = $this->AADTokenPayload->oid;

            $apiEndpoint = "https://graph.microsoft.com/v1.0/users/$oid/photo/\$value";
            $options = ['http' => ['header' => "Authorization: Bearer $AAD_ACCESS_TOKEN"]];
            $context = stream_context_create($options);
            $userImg = file_get_contents($apiEndpoint, false, $context);
            $fileSaveSuccess = file_put_contents($_SERVER["DOCUMENT_ROOT"]."/users/pics/$this->userGID.png", $userImg);
            if(!$fileSaveSuccess)
                throw new Exception("AADAuth: User($this->userGID) image could not be saved. " . error_get_last()["message"]);
        } catch (Throwable $e){
            error_log($e->getMessage());
        }
    }

    protected function saveUserAADRefreshToken($AAD_REFRESH_TOKEN){
        try{
            $query = "INSERT IGNORE INTO user_tokens(oid, email, refreshToken) VALUES(:oid, :email, :refreshToken) ON DUPLICATE KEY UPDATE refreshToken=VALUES(refreshToken), email=VALUES(email)";
            DbManager::fetchPDOQuery('php_auth', $query, [
                ':oid' => $this->AADTokenPayload->oid,
                ':email' => $this->AADTokenPayload->mail ?? $this->AADTokenPayload->unique_name,
                ':refreshToken' => $AAD_REFRESH_TOKEN
            ]);
        } catch (Throwable $e){
            error_log($e->getMessage());
        }

    }

    public function getAccessTokenName(){
        return $this->ACCESS_TOKEN_NAME;
    }

    public function getRefreshTokenName(){
        return $this->REFRESH_TOKEN_NAME;
    }

    public function redirect($redirectURL)
    {
        header("Location: $redirectURL", true, 302);
        exit;
    }
}