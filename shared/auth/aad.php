<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/auth/AADBase.php";
error_reporting(E_ERROR | E_PARSE);

class AADAuth extends AADBase{
    function __construct(bool $init = true){
        $this->initEnv();
        $this->checkAuth($init);
    }

    private function checkAuth($init){
        if (!$init) {
            return;
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $accessToken = $_COOKIE[$this->ACCESS_TOKEN_NAME];
        $refreshToken = $_COOKIE[$this->REFRESH_TOKEN_NAME];
        $this->baseURL = "https://login.microsoftonline.com/$this->TENANT_ID";

        if ($accessToken && $refreshToken){
            $isAccessTokenExpired = time() > $this->getTokenPart($accessToken, 1)->exp;
            $isAccessTokenValid = $this->is_jwt_signature_valid($accessToken, 'SHA512', $this->ONEX_TOKEN_SECRET);
            $isRefreshTokenValid = $this->is_jwt_valid($refreshToken);
            if ($isAccessTokenValid && $isRefreshTokenValid) {
                if ($isAccessTokenExpired)
                    $this->refreshConditions();
            } else {
                $this->deleteSecureCookie($this->ACCESS_TOKEN_NAME, '');
                $this->deleteSecureCookie($this->REFRESH_TOKEN_NAME, '');
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

            $AAD_REFRESH_TOKEN = $AADTokens["refresh_token"];

            $this->verifyAADAccessToken($AAD_ACCESS_TOKEN);
            $this->generateOnexTokens();
            $this->saveUserImage($AAD_ACCESS_TOKEN);
            $this->saveUserAADRefreshToken($AAD_REFRESH_TOKEN);
            $this->redirect(rawurldecode($this->onexRedirectURL));
        }
        else
            $this->noLogin();
    }

}
