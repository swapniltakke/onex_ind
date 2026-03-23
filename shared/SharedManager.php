<?php
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/DbManager.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/MailManager.php';

error_reporting(E_ERROR | E_PARSE);

class SharedManager
{
    public static $user_os = "";
    public static $user_browser = "";
    public static $user_ip = "";
    public static $email = "";
    public static $whom = null;
    public static $gid = "";
    public static $name = "";
    public static $surname = "";
    public static $fullname = "";
    public static $nativeName = "";
    public static $modulesStr = "";
    public static $functionsStr = "";
    public static $userGroupId = 0;
    public static $isSupplier = 0;
    public static $userOrgCode = "";
    public static $registryNo = "";
    public static $hostUrl = "";
    
    public static function getUserIP()
    {
        $ip = $_SERVER["HTTP_CLIENT_IP"] ??
            $_SERVER["HTTP_X_FORWARDED_FOR"] ??
            $_SERVER["HTTP_X_FORWARDED"] ??
            $_SERVER["HTTP_FORWARDED_FOR"] ??
            $_SERVER["HTTP_FORWARDED"] ??
            $_SERVER["REMOTE_ADDR"];
        if (strpos($ip, ',') > 0)
            $ip = substr($ip, 0, strpos($ip, ','));
        return $ip;
    }

    public static function setUserPermissions($newAccessToken = null)
    {
        $payload = self::getAccessTokenPayload($newAccessToken);
        self::$modulesStr = $payload->modulesStr;
        self::$functionsStr = $payload->functionsStr;
        self::$userGroupId = $payload->group_id;
        self::$userOrgCode = $payload->org_code;
        self::$isSupplier = $payload->is_supplier;
    }

    public static function getFromSharedEnv($keyword){
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/composer/vendor/autoload.php";

        $dotenv = Dotenv\Dotenv::createImmutable($_SERVER["DOCUMENT_ROOT"] . "/shared");
        $dotenv->load();
        return $_ENV[$keyword] ?? null;
    }

    private static function getAccessTokenPayload($newAccessToken = null)
    {
        $AADInstance = self::getAADAuthInstance(false);
        $accessToken = $newAccessToken ?? $_COOKIE[$AADInstance->getAccessTokenName()];
        return $AADInstance->getTokenPart($accessToken, 1);
    }

    private static function getAADAuthInstance(bool $init = true)
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/auth/aad.php";
        return new AADAuth($init);
    }

    public static function getOSForUser()
    {
        $user_agent = @$_SERVER['HTTP_USER_AGENT'];
        $os_platform = "Unknown OS Platform";

        $os_array = array(
            '/windows nt 10/i' => 'Windows 10',
            '/windows nt 6.3/i' => 'Windows 8.1',
            '/windows nt 6.2/i' => 'Windows 8',
            '/windows nt 6.1/i' => 'Windows 7',
            '/windows nt 6.0/i' => 'Windows Vista',
            '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
            '/windows nt 5.1/i' => 'Windows XP',
            '/windows xp/i' => 'Windows XP',
            '/windows nt 5.0/i' => 'Windows 2000',
            '/windows me/i' => 'Windows ME',
            '/win98/i' => 'Windows 98',
            '/win95/i' => 'Windows 95',
            '/win16/i' => 'Windows 3.11',
            '/macintosh|mac os x/i' => 'Mac OS X',
            '/mac_powerpc/i' => 'Mac OS 9',
            '/linux/i' => 'Linux',
            '/ubuntu/i' => 'Ubuntu',
            '/iphone/i' => 'iPhone',
            '/ipod/i' => 'iPod',
            '/ipad/i' => 'iPad',
            '/android/i' => 'Android',
            '/blackberry/i' => 'BlackBerry',
            '/webos/i' => 'Mobile'
        );

        foreach ($os_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $os_platform = $value;

        return $os_platform;
    }

    public static function getBrowserForUser()
    {
        global $user_agent;

        $browser = "Unknown Browser";
        $browser_array = array(
            '/msie/i' => 'Internet Explorer',
            '/trident/i' => 'Internet Explorer',
            '/firefox/i' => 'Firefox',
            '/safari/i' => 'Safari',
            '/chrome/i' => 'Chrome',
            '/edge/i' => 'Edge',
            '/opera/i' => 'Opera',
            '/netscape/i' => 'Netscape',
            '/maxthon/i' => 'Maxthon',
            '/konqueror/i' => 'Konqueror',
            '/mobile/i' => 'Safari'
        );

        foreach ($browser_array as $regex => $value)
            if (preg_match($regex, $user_agent))
                $browser = $value;

        return $browser;
    }

    public static function saveLog($appName, $action)
    {
        $userInfo = self::getAccessTokenPayload();
        $user_os = self::getOSForUser();
        $user_browser = self::getBrowserForUser();
        $user_ip =self::getUserIP();
        $whom = $userInfo->mail;
        $gid = $userInfo->gid;
        $name = $userInfo->name;
        $surname = $userInfo->surname;

        $query_log = "
            INSERT INTO $appName (whom, what, name, surname, gid, operating_system, browser, ip) 
            VALUES (:whom, :action, :name, :surname, :gid, :user_os, :user_browser, :user_ip)";
        $pdo_params = [
            ":whom" => $whom,
            ":action" => $action,
            ":name" => $name,
            ":surname" => $surname,
            ":gid" => $gid,
            ":user_os" => $user_os,
            ":user_browser" => $user_browser,
            ":user_ip" => $user_ip
        ];

        if (!in_array(self::getUserIP(), ['::1', '127.0.0.1'])) {
            DbManager::fetchPDOQuery('logs', $query_log, $pdo_params);
        }
    }

    public static function cURL_GET($dataArray, $url)
    {
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

    public static function cURL_POST($dataArray, $url)
    {
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

    public static function hasAccessRight(mixed $moduleID, mixed $function = null)
    {
        if (!is_array($moduleID) && !is_int($moduleID))
            return false;
        if ($function && !is_array($function) && !is_int($function))
            return false;
        if (is_int($moduleID) && !($moduleID > 0))
            return false;
        if (is_array($moduleID) && count($moduleID) === 0)
            return false;
        if (is_int($function) && !($function > 0))
            return false;
        if (is_array($function) && count($function) === 0)
            return false;

        $userModules = array_filter(array_map('intval', explode('|', self::$modulesStr)), function ($n) {
            return $n > 0 && $n === (int)$n;
        });
        $userFunctions = array_filter(array_map('intval', explode('|', self::$functionsStr)), function ($n) {
            return $n > 0 && $n === (int)$n;
        });

        if (count($userModules) === 0)
            return false;
        if ((is_int($moduleID) && !in_array($moduleID, $userModules)) || (is_array($moduleID) && !empty(array_diff($moduleID, $userModules))))
            return false;
        if ((is_int($function) && !in_array($function, $userFunctions)) || (is_array($function) && !empty(array_diff($function, $userFunctions))))
            return false;

        return true;
    }

    public static function checkAuthToModule(mixed $moduleID, mixed $function = null, string $redirectURL = "")
    {
        if (!is_array($moduleID) && !is_int($moduleID))
            throw new InvalidArgumentException(__FUNCTION__ . ' expecting array or integer as moduleID, got ' . gettype($moduleID));
        if ($function && !is_array($function) && !is_int($function))
            throw new InvalidArgumentException(__FUNCTION__ . ' expecting array or integer as function, got ' . gettype($function));
        if (is_int($moduleID) && !($moduleID > 0))
            throw new InvalidArgumentException("Module can not be less than 1");
        if (is_array($moduleID) && count($moduleID) === 0)
            throw new InvalidArgumentException("Modules array can not be empty");
        if (is_int($function) && !($function > 0))
            throw new InvalidArgumentException("Function can not be less than 1");
        if (is_array($function) && count($function) === 0)
            throw new InvalidArgumentException("Function array can not be empty");
        $REDIRECT_PATH = self::getAADAuthInstance(false)->getHttpHostURL();
        if ($redirectURL && !str_starts_with($redirectURL, $REDIRECT_PATH))
            throw new InvalidArgumentException("Can not redirect other than $REDIRECT_PATH");

        $userAccessToken = self::getAccessTokenPayload();
        $userEmail = $userAccessToken->mail;
        $userName = $userAccessToken->name;
        $userSurname = $userAccessToken->surname;
        $userGID = $userAccessToken->gid;

        $userModules = array_filter(array_map('intval', explode('|', $userAccessToken->modulesStr)), 'is_int');
        $userFunctions = array_filter(array_map('intval', explode('|', $userAccessToken->functionsStr)), 'is_int');

        if (!$userEmail)
            self::getAADAuthInstance(false);

        $REDIRECT_URL = !$redirectURL ? "/shared/screens/notallowed.php?email=$userEmail&name=$userName&surname=$userSurname&department=$userGID&gid=$userGID&link=$REDIRECT_PATH" : $redirectURL;
        if (count($userModules) === 0)
            self::checkMissingAuth($moduleID, $function, $REDIRECT_URL);
        if ((is_int($moduleID) && !in_array($moduleID, $userModules)) || (is_array($moduleID) && !empty(array_diff($moduleID, $userModules))))
            self::checkMissingAuth($moduleID, $function, $REDIRECT_URL);
        if ((is_int($function) && !in_array($function, $userFunctions)) || (is_array($function) && !empty(array_diff($function, $userFunctions))))
            self::checkMissingAuth($moduleID, $function, $REDIRECT_URL);

        return true;
    }

    private static function checkMissingAuth($moduleID, $function, $REDIRECT_URL)
    {
        $AADAuth = self::getAADAuthInstance(false);
        $newTokenPayload = $AADAuth->refreshConditions();
        $newTokenPayloadData = $AADAuth->getTokenPart($newTokenPayload, 1);
        $modulesStr = $newTokenPayloadData->modulesStr;
        $functionsStr = $newTokenPayloadData->functionsStr;
        $userModules = array_filter(array_map('intval', explode('|', $modulesStr)), function ($n) {
            return $n > 0 && $n === (int)$n;
        });
        $userFunctions = array_filter(array_map('intval', explode('|', $functionsStr)), function ($n) {
            return $n > 0 && $n === (int)$n;
        });

        if (count($userModules) === 0)
            $AADAuth->redirect($REDIRECT_URL);
        if ((is_int($moduleID) && !in_array($moduleID, $userModules)) || (is_array($moduleID) && !empty(array_diff($moduleID, $userModules))))
            $AADAuth->redirect($REDIRECT_URL);
        if ((is_int($function) && !in_array($function, $userFunctions)) || (is_array($function) && !empty(array_diff($function, $userFunctions))))
            $AADAuth->redirect($REDIRECT_URL);

        header("Refresh:0");
        exit;
    }

    public static function getUser()
    {
        $tokenPayload = self::getAccessTokenPayload();
        return [
            "Name" => $tokenPayload->name,
            "Surname" => $tokenPayload->surname,
            "FullName" => $tokenPayload->name . " " . $tokenPayload->surname,
            "Email" => $tokenPayload->mail,
            "GID" => $tokenPayload->gid,
            "OrgCode" => $tokenPayload->org_code,
            "IP" => self::getUserIP(),
            "Browser" => self::getBrowserForUser(),
            "OS" => self::getOSForUser(),
            "Modules" => array_map('intval', explode('|', $tokenPayload->modulesStr)),
            "Functions" => array_map('intval', explode('|', $tokenPayload->functiosStr)),
            "GroupID" => $tokenPayload->group_id
        ];
    }

    public static function logOut(){
        $AADAuth = self::getAADAuthInstance(false);
        $AADAuth->deleteSecureCookie($AADAuth->getAccessTokenName(), '');
        $AADAuth->deleteSecureCookie($AADAuth->getRefreshTokenName(), '');
        $AADAuth->redirect((isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
    }

    public static function downloadFile($file, $downloadname)
    {
        $filename = basename($file);
        $mimeType = mime_content_type($file);
        $filesize = filesize($file);
        header('Content-Description: File Transfer');
        header("Content-Type: $mimeType");
        header("Content-Length: $filesize");
        header('Content-Transfer-Encoding: binary');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Expires: 0');
        header('Cache-Control: must revalidate');
        header('Pragma: public');
        ob_clean();
        flush();
        readfile($file);
    }

    public static function formatName($full_name) {
        $names = explode(' ', trim($full_name));
        $first_name = array_shift($names);
        $formatted_name = implode(' ', $names) . ' ' . $first_name;
        return $formatted_name;
    }

    public static function ldap(
        string $name = null,
        string $surname = null,
        string $email = null,
        string $gid = null,
        string $sam = null,
        string $title = null,
        string $country = null,
        string $city = null,
        string $department = null,
        string $mobile = null,
        mixed $showFunctionalAccount = 0)
    {
        // SharedManager::print('$name');
        // SharedManager::print('$surname');
        // SharedManager::print('$email');
        // SharedManager::print('$gid');
        // SharedManager::print('$sam');
        // SharedManager::print('$country');
        // SharedManager::print('$city');
        // SharedManager::print('$title');
        // SharedManager::print('$department');
        // SharedManager::print('$mobile');

        $searchFilters = array();

        if ($name) $searchFilters[] = "(givenname=$name)";
        if ($surname) $searchFilters[] = "(sn=$surname)";
        if ($email) $searchFilters[] = "(mail=$email)";
        if ($gid) $searchFilters[] = "(siemens-gid=$gid)";
        if ($sam) $searchFilters[] = "(samaccountname=$sam)";
        if ($country) $searchFilters[] = "(c=$country)";
        if ($city) $searchFilters[] = "(l=$city)";
        if ($title) $searchFilters[] = "(title=$title)";
        if ($department) $searchFilters[] = "(department=$department)";
        if ($mobile) $searchFilters[] = "(mobile=$mobile)";

        if(!count($searchFilters)) {
            error_log("No parameter given for LDAP search!", 0);
            http_response_code(400);
            die("No parameter given for LDAP search!");
        }

        $searchFilter = "(&(objectClass=user)" . implode('', $searchFilters) . ")";

        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/composer/vendor/autoload.php";

        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
        $dotenv->load();

        $LDAP_SERVER = $_ENV['LDAP_SERVER'];
        $LDAP_USERNAME = $_ENV['LDAP_USERNAME'];
        $LDAP_PASSWORD = $_ENV['LDAP_PASSWORD'];
        // Attempt to establish an LDAPS connection with TLS options
        $ldap_conn = ldap_connect($LDAP_SERVER, 3269);

        if (!$ldap_conn) {
            $ldap_error = ldap_error($ldap_conn);  // Get the LDAP error message
            error_log("LDAP Error: $ldap_error", 0);  // Log the error message

            // Set the HTTP response code to 500
            http_response_code(500);

            // Return an error message to the user
            $error_message = "LDAP authentication failed. Please contact the administrator.";

        }
        // Set LDAP options for secure communication
        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        // Attempt to bind using the provided credentials
        $ldap_bind = ldap_bind($ldap_conn, $LDAP_USERNAME, $LDAP_PASSWORD);

        if (!$ldap_bind) {
            $jsonOutput = json_encode("connect error", JSON_PRETTY_PRINT);
            echo $jsonOutput;
            http_response_code(500);
            exit;
        }

        // Continue with LDAP search or other operations
        $searchBaseDN = 'dc=ad001,dc=siemens,dc=net'; // Replace with the base DN where your users are located
        $searchFilter = "(&(objectClass=user)" . implode('', $searchFilters) . ")";

        $attributes = array(
            'samaccountname',  // User's account name (username)
            'gid',     // User's display name (full name)
            'sn',     // User's display name (full name)
            'c',     // User's display name (full name)
            'l',     // User's display name (full name)
            'mail',            // User's email address
            'department',            // User's email address
            'directreports',            // User's email address
            'siemens-gid',            // User's email address
            'title',            // User's email address
            'mobile',            // User's email address
            'office',            // User's email address
            'displayname',            // User's email address
            'siemens-costlocation'            // User's email address
        );

        $searchResults = ldap_search($ldap_conn, $searchBaseDN, $searchFilter);

        if (!$searchResults) {
            die("LDAP search failed.");
        }

        $userData = ldap_get_entries($ldap_conn, $searchResults);

        ldap_unbind($ldap_conn);

        if ($userData['count'] == 0) {
            return [];
        }

        $results = array();
        foreach($userData as $user) {

            // Extract the basic user information
            $username = $user['samaccountname'][0];
            $realgid = $user['siemens-gid'][0];

            if(!$showFunctionalAccount) {
                if(strpos($realgid, '-Owner'))
                    continue;
            }

            if ($username === null) {
                continue;
            }

            $name = $user['givenname'][0];
            $surname = $user['sn'][0];
            $title = $user['title'][0];
            $manager =  self::formatName(preg_replace('/(CN=)(.*)( .*)(,OU=Users,.*)/', '$2', $user['manager'][0]));
            $managergid = preg_replace('/(.* )(.*)(,OU=Users.*)/', '$2', $user['manager'][0]);
            $email = $user['mail'][0];
            $country = $user['c'][0];
            $office = $user['physicaldeliveryofficename'][0];
            $city = $user['l'][0];
            $department = $user['department'][0];
            $displayname = $user['displayname'][0];
            $mobile = $user['mobile'][0];
            $costcenter = $user['siemens-costlocation'][0];
            $directreports = isset($user['directreports']) && is_array($user['directreports'])
                ? implode(' - ', $user['directreports'])
                : 'No direct reports';

            if($directreports != 'No direct reports') {
                preg_match_all('/CN=([^,]+),OU=Users/', $directreports, $matches);
                $extractedData = $matches[1];
                $convertedData = [];
                foreach ($extractedData as $data) {
                    $parts = explode(" ", $data);
                    $gid = array_pop($parts);
                    $lastName = array_shift($parts);
                    $firstName = implode(" ", $parts);
                    $convertedData[] = $firstName . " " . $lastName . "|" . $gid;
                }

                $directreports =  $convertedData;
            }

            // Build an array with the current users data
            $userDataArray = array(
                'GID' => $realgid,
                'OldGID' => $username,
                'Name' => $name,
                'Surname' => $surname,
                'DisplayName' => $displayname,
                'Manager' => $manager,
                'ManagerGID' => $managergid,
                'Title' => $title,
                'Email' => $email,
                'Mobile' => $mobile,
                'City' => $city,
                'Country' => $country,
                'Office' => $office,
                'Department' => $department,
                'CostCenter' => $costcenter,
                'DirectReports' => $directreports,
            );

            $results[] = $userDataArray;
        }

        return $results;
    }

    public static function print($data) {
        $backtrace = debug_backtrace();
        $line_number = $backtrace[0]['line'];
        echo "<pre>";
        echo "\Printed on Line number: ".$line_number."\n";
        print_r($data);
        echo "</pre>";
    }

    public static function get_ip_address() {
        if (stripos($_SERVER['HTTP_HOST'], 'localhost') !== false) {
            return gethostbyname(gethostname());
        } else {
            return $_SERVER['REMOTE_ADDR'];
        }
    }

    public static function encrypt_password($password = "") {
        $encryptionMethod = "AES-256-CBC";
        $secret_key = getenv("DB_CREDENTIALS_THA");
        $iv = openssl_cipher_iv_length($encryptionMethod);
        $encryptedPassword = openssl_encrypt($password, $encryptionMethod, $secret_key, 0, $iv);
        return $encryptedPassword;
    }

    public static function decrypt_password($password = "") {
        $encryptionMethod = "AES-256-CBC";
        $secret_key = getenv("DB_CREDENTIALS_THA");
        $iv = openssl_cipher_iv_length($encryptionMethod);
        $decryptedPassword = openssl_decrypt($password, $encryptionMethod, $secret_key, 0, $iv);
        return $decryptedPassword;
    }
}