<?php
date_default_timezone_set(@date_default_timezone_get());
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/auth/AADBase.php";
error_reporting(E_ERROR | E_PARSE);

Class CronAuth extends AADBase{
    private ?string $IP;
    private array $allowedIPs = [
        "::1",
        "127.0.0.1",
        "132.186.79.45",
        "140.231.42.187"
    ];

    public function __construct(){
        $this->IP = getUserIP();
        if(!in_array($this->IP, $this->allowedIPs))
            $this->checkAuth();
    }

    private function checkAuth(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->initEnv();
        $accessToken = $_COOKIE[$this->ACCESS_TOKEN_NAME];
        $refreshToken = $_COOKIE[$this->REFRESH_TOKEN_NAME];
        $this->baseURL = "https://login.microsoftonline.com/$this->TENANT_ID";

        if ($accessToken && $refreshToken) {
            $isAccessTokenExpired = time() > $this->getTokenPart($accessToken, 1)->exp;
            $isAccessTokenValid = $this->is_jwt_signature_valid($accessToken, 'SHA512', $this->ONEX_TOKEN_SECRET);
            $isRefreshTokenValid = $this->is_jwt_valid($refreshToken);

            if ($isAccessTokenValid && $isRefreshTokenValid) {
                if ($isAccessTokenExpired)
                    $this->refreshConditions();

                if(!$this->isAdminUser())
                    $this->notAllowed();
                //Authenticated if code reaches here
            } else {
                $this->deleteSecureCookie($this->ACCESS_TOKEN_NAME, '');
                $this->deleteSecureCookie('refreshToken', '');
                $this->noLogin();
            }
        }
        else if($_GET["code"]){
            $_CODE = $_GET["code"];
            $this->onexRedirectURL = $_GET["state"];

            $this->valideStateParameter($this->onexRedirectURL, $_CODE);

            $AADTokens = $this->getAADTokens($_CODE);

            $AAD_ACCESS_TOKEN = $AADTokens["access_token"];
            $this->AADTokenPayload = $this->getTokenPart($AAD_ACCESS_TOKEN, 1);

            $this->verifyAADAccessToken($AAD_ACCESS_TOKEN);
            $this->generateOnexTokens();
            $this->redirect(rawurldecode($this->onexRedirectURL));
        }
        else
            $this->noLogin();
    }

    private function isAdminUser(): bool{
        $accessTokenPayload = $this->getTokenPart($_COOKIE[$this->ACCESS_TOKEN_NAME], 1);
        $userGroupID = $accessTokenPayload->group_id;
        return $userGroupID === 2;
    }

    private function notAllowed(){
        $accessTokenPayload = $this->getTokenPart($_COOKIE[$this->ACCESS_TOKEN_NAME], 1);
        $userEmail = $accessTokenPayload->mail;
        $userName = $accessTokenPayload->name;
        $userSurname = $accessTokenPayload->surname;
        $userGID = $accessTokenPayload->gid;
        $REDIRECT_PATH = $this->getCurrentUrl();
        $this->redirect("/shared/screens/notallowed.php?email=$userEmail&name=$userName&surname=$userSurname&department=$userGID&gid=$userGID&link=$REDIRECT_PATH");
    }
}

new CronAuth();
