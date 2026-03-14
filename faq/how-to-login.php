<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="/css/nologin.css?<?= time()?>">
    <link rel="stylesheet" href="/css/helpLogin.css?<?= time();?>"/>
    <script src="/js/jquery.min.js"></script>
    <title>Login</title>

</head>
<body>

<div class="ui container grid center aligned mainSection">
    <section class="faq-section">
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="faq-title text-center">
                        <div style="display: flex;justify-content: center">
                            <span class="animate-character logo-helplogin">OneX</span>
                        </div>
                    </div>
                    <div class="faq-title text-center">
                        <div id="languageSelection" style="display: flex;justify-content: center;margin: 5px 0px 10px 0px;">

                        </div>
                    </div>
                    <div class="faq-title text-center">
                        <h2><span data-translate="helpLogin-howto">How to login?</span></h2>
                    </div>
                </div>
                <div class="col-md-6 offset-md-3">
                    <div class="faq" id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-1" data-aria-expanded="true" data-aria-controls="faqCollapse-1">
                                        <span class="badge">1</span>
                                        <span data-translate="helpLogin-login-onex">Login to <b><?php echo $_SERVER['HTTP_HOST'] ?></b></span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-2" data-aria-expanded="false" data-aria-controls="faqCollapse-2">
                                        <span class="badge">2</span>
                                        <span data-translate="helpLogin-btn-clickAzure">Click on AzureAD button</span>
                                    </h5>
                                </div>
                            </div>
                            <div id="faqCollapse-2" class="collapse" aria-labelledby="faqHeading-2" data-parent="#accordion">
                                <div class="card-body df-jcc-fdc">
                                    <img src="/images/how-to-login/onex-login-0.jpeg">
                                    <p class="mt-1r">
                                        <span data-translate="helpLogin-btn-clickAzure-desc">
                                            After logging in to <?php echo $_SERVER['HTTP_HOST'] ?> click the AzureAD button on the page that appears.
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-3" data-aria-expanded="false" data-aria-controls="faqCollapse-3">
                                        <span class="badge">3</span>
                                        <span data-translate="helpLogin-btn-loginSiemens">
                                            Log in to your Siemens account (if you are not already logged in)
                                        </span>
                                    </h5>
                                </div>
                            </div>
                            <div id="faqCollapse-3" class="collapse" aria-labelledby="faqHeading-3" data-parent="#accordion">
                                <div class="card-body df-jcc-fdc">
                                    <img src="/images/how-to-login/onex-login-1.0.jpeg">
                                    <p class="mt-1r">
                                        <span data-translate="helpLogin-btn-loginSiemens-desc">
                                            After clicking the Azure AD button, log in with your Siemens e-mail address and password on the page you are directed to
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-4" data-aria-expanded="false" data-aria-controls="faqCollapse-4">
                                        <span class="badge">4</span>
                                        <span data-translate="helpLogin-btn-loginSiemens-twoStep">
                                            Complete the two-step verification
                                        </span>
                                    </h5>
                                </div>
                            </div>
                            <div id="faqCollapse-4" class="collapse" aria-labelledby="faqHeading-4" data-parent="#accordion">
                                <div class="card-body df-jcc-fdc">
                                    <img src="/images/how-to-login/onex-login-1.1.jpeg">
                                    <p class="mt-1r">
                                        <span data-translate="helpLogin-btn-loginSiemens-twoStep-desc">
                                            After logging in with your e-mail address and password, complete the two-step verification
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-5" data-aria-expanded="false" data-aria-controls="faqCollapse-5">
                                        <span class="badge">5</span>
                                        <span data-translate="helpLogin-permission">
                                            Grant Siemens AzureAD permission (if you haven't already granted it)
                                        </span>
                                    </h5>
                                </div>
                            </div>
                            <div id="faqCollapse-5" class="collapse" data-parent="#accordion">
                                <div class="card-body df-jcc-fdc">
                                    <img src="/images/how-to-login/onex-login-4.jpeg">
                                    <p class="mt-1r">
                                        <span data-translate="helpLogin-permission-desc">
                                            Grant Siemens AzureAD permission on the page that appears after logging in. (This permission is for your basic profile info only)
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header">
                                <div class="mb-0">
                                    <h5 class="faq-title" data-toggle="collapse" data-target="#faqCollapse-6" data-aria-expanded="false" data-aria-controls="faqCollapse-6">
                                        <span class="badge">6</span>
                                        <span data-translate="helpLogin-here-is-onex">
                                            Here is OneX. Enjoy your work!
                                        </span>
                                    </h5>
                                </div>
                            </div>
                            <div id="faqCollapse-6" class="collapse" aria-labelledby="faqHeading-6" data-parent="#accordion">
                                <div class="card-body df-jcc-fdc">
                                    <img src="/images/how-to-login/onex-login-5.jpeg">
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="/shared/language/language-converter.js?<?=time()?>"></script>
<script src="/shared/language/language-button.js"></script>
<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/language/translator.php"; ?>
<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function() {
        const translator = new Translator('how-to-login', '#languageSelection', ['en'], 'en')
    });
</script>
</body>
</html>