<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/auth/aad.php";
$AADAuthInstance = new AADAuth(false);
$redirectURL = $AADAuthInstance->getAADRedirectURL();
?>

<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <script src="/js/jquery.min.js"></script>
        <title>Login</title>
    </head>
    <body>
        <div class="login-page-container">
            <div class="login-page-header">
                <div class="siemens-onex-container">
                    <img src="/images/logo-light.svg" alt="" />
                </div>
            </div>
            <div class="login-page-body">
                <video autoplay muted loop id="main-video">
                    <source
                        src="/images/background.mp4"
                        type="video/mp4"
                    />
                </video>
                <div class="content-container">
                    <img src="/images/robot5.gif" alt="" srcset="" />
                </div>
                <div class="login-components">
                    <div class="login-labels">
                        <h1>
                            Log in to <span class="main-onex-text">OneX</span>
                        </h1>
                        <h5>Click to Login with Entra ID button</h5>
                    </div>
                    <div class="login-button-container">
                        <button class="login-button">
                            <img src="/images/entraidicon.png" alt="" />
                            <span>Log in with Microsoft Entra ID</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="login-page-footer">
                <div class="footer-info-container">
                    <img src="/images/onex.png" style="width: 15%;">
                    <div class="footer-info-content">
                        <h2>
                            For
                            <span class="for-group-text">SI EA O AIS THA</span>
                        </h2>
                        <h2>
                            By
                            <span class="by-dgt-text"
                                >Digital Transformation Team (DGT)</span
                            >
                        </h2>
                    </div>
                </div>
                <div class="techs-container">
                    <span class="tech-item">SAP</span>
                    <span class="tech-item">MTool</span>
                    <span class="tech-item">Snowflake</span>
                </div>
            </div>
        </div>
        <?php require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/translator.php"; ?>
        <script>
            document.querySelector("#main-video").playbackRate = 0.33;
            document.querySelector(".login-button").addEventListener("click", (e)=>{
                window.location = "<?php echo $redirectURL; ?>"
            })
        </script>

        <style>
            /* IX Colors Setup Start*/

            :root {
                --theme-color-primary: #0cc;
                --theme-color-secondary: #000028;
            }

            /* IX Colors Setup End*/

            * {
                box-sizing: border-box;
                padding: 0;
                margin: 0;
            }

            body {
                background-color: var(--theme-color-secondary);
            }

            .login-page-header {
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                width: 100%;
                background: var(--theme-color-secondary);
                min-height: 64px;
                padding: 0 32px;
                user-select: none;
            }

            .siemens-onex-container {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                gap: 12px;
            }

            .onex-header-text {
                font-size: 30px;
                line-height: 24px;
                border-left: 3px solid white;
                padding-left: 8px;
            }

            .login-page-container {
                display: flex;
                flex-direction: column;
                color: white;
                width: 100%;
                height: 100vh;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            }

            #main-video {
                position: absolute;
                width: 100%;
                height: 100%;
                max-width: 100%;
                max-height: 100%;
                z-index: -1;
                object-fit: fill;
            }

            .login-page-body {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: space-between;
                width: 100%;
                height: 80%;
            }

            .content-container {
                display: flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                height: 400px;
                position: relative;
                left: 10%;
                user-select: none;
            }

            .content-container > * {
                height: 100%;
            }

            .login-components {
                display: flex;
                flex-direction: column;
                align-items: start;
                justify-content: space-evenly;
                background-color: rgb(
                    from var(--theme-color-secondary) r g b / 75%
                );
                padding: 24px 48px 48px 48px;
                padding-right: 192px;
                gap: 48px;
                text-align: start;
                height: 420px;
                box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
            }

            .login-labels {
                display: flex;
                flex-direction: column;
                align-items: start;
                justify-content: start;
                gap: 12px;
            }

            .login-labels h1 {
                font-size: 48px;
            }

            .login-labels h5 {
                font-size: 18px;
                border-left: 6px solid var(--theme-color-primary);
                padding-left: 6px;
                color: #ffffffba;
                position: relative;
                left: 3px;
                font-weight: 500;
            }

            .main-onex-text {
                color: var(--theme-color-primary);
                position: relative;
                -webkit-mask-image: linear-gradient(
                    -75deg,
                    rgba(0, 0, 0, 0.8) 30%,
                    #000 50%,
                    rgba(0, 0, 0, 0.8) 70%
                );
                -webkit-mask-size: 200%;
                animation: shine 1.18s linear infinite;
            }

            @keyframes shine {
                from {
                    -webkit-mask-position: 150%;
                }
                to {
                    -webkit-mask-position: -50%;
                }
            }

            .login-button-container {
                width: 100%;
            }

            .login-button {
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                background-color: #92969d37;
                color: var(--theme-color-primary);
                border: none;
                border: 4px solid var(--theme-color-primary);
                border-radius: 2px;
                width: 100%;
                font-size: 24px;
                line-height: 24px;
                padding: 12px 10px;
                font-weight: 700;
                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                transition: 0.2s;
                gap: 8px;
            }

            .login-button:hover {
                cursor: pointer;
                background-color: #92969d5a;
                transition: 0.2s;
            }

            .login-button:active {
                background-color: #92969d46;
                transition: 0.2s;
            }

            .login-button img {
                width: 30px;
                height: 30px;
            }

            .login-page-footer {
                display: flex;
                flex-direction: row;
                width: 100%;
                background: var(--theme-color-secondary);
                opacity: 0.9;
                flex: 1;
                justify-content: center;
                align-items: center;
                gap: 64px;
                padding: 16px 0;
            }

            .footer-info-container {
                display: flex;
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 24px;
            }

            .footer-info-onex-text {
                font-size: 48px;
                font-weight: 700;
                color: var(--theme-color-primary);
                border-right: 4px solid var(--theme-color-primary);
                padding-right: 32px;
            }

            .footer-info-content {
                display: flex;
                flex-direction: column;
            }

            .for-group-text {
                color: #00ffb9;
            }

            .by-dgt-text {
                color: var(--theme-color-primary);
            }

            .techs-container {
                display: flex;
                flex-direction: column;
                align-items: start;
                gap: 8px;
                max-height: 90px;
                flex-wrap: wrap;
                user-select: none;
            }

            .tech-item {
                color: var(--theme-color-secondary);
                padding: 3px 6px;
                background-color: var(--theme-color-primary);
                font-weight: 600;
                border: 2px;
                width: 96px;
                text-align: center;
                border-radius: 2px;
            }

            @media only screen and (max-width: 1023px) {
                .tech-item {
                    font-size: 8px;
                    width: max-content;
                }

                .login-labels h1 {
                    font-size: 42px;
                }

                .login-labels h5 {
                    font-size: 16px;
                }

                .login-components {
                    height: 360px;
                    padding-right: 96px;
                }

                .content-container {
                    height: 360px;
                    bottom: 16px;
                    left: 5%;
                }

                .login-button {
                    font-size: 16px;
                    text-align: start;
                }

                .login-button img {
                    width: 21px;
                    height: 21px;
                }
            }

            @media only screen and (max-width: 720px) {
                .content-container {
                    display: none !important;
                }

                .login-page-body {
                    justify-content: center !important;
                    align-items: center !important;
                }

                .login-components {
                    width: 90%;
                    padding: 32px;
                }

                .login-page-footer {
                    flex-direction: column;
                }

                .footer-info-onex-text {
                    font-size: 24px;
                }
                .footer-info-content {
                    font-size: 8px;
                }

                .login-page-footer {
                    gap: 16px;
                }

                .techs-container {
                    width: 100%;
                    flex-wrap: wrap;
                    flex-direction: row;
                    justify-content: center;
                    align-items: center;
                    order: -1;
                }

                .tech-item {
                    font-size: 8px;
                    width: max-content;
                }

                .login-labels h1 {
                    font-size: 32px;
                }

                .login-labels h5 {
                    font-size: 16px;
                }

                .login-components {
                    height: auto;
                }

                .login-button {
                    font-size: 12px;
                    text-align: start;
                }

                .login-button img {
                    width: 21px;
                    height: 21px;
                }
            }
        </style>
    </body>
</html>