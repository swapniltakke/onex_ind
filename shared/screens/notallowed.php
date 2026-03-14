<?php
$email = $_GET["email"];
$name = $_GET["name"];
$surname = $_GET["surname"];
$department = $_GET["department"];
$gid = $_GET["gid"];
$link = $_GET["link"];
if(!$email && !$name && !$surname && !$department && !$gid && !$link){
    require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";
    returnHttpResponse(400, "One of the required GET parameter is missing. \$_GET content: " . json_encode(  $_GET));
}

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/MailManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/SharedManager.php";
$selfOrgCode = SharedManager::getFromSharedEnv("SELF_ORG_CODE");
$contactEmail = SharedManager::getFromSharedEnv("CONTACT_EMAIL");
$userIP = SharedManager::getUserIP();

$subject = "[OneX Unauthorized User Access] $email - $name $surname ($gid) $department";
$body = "Hi,<br><br>
	An unauthorized access has been occurred.<br/>
	IP Address: $userIP<br/><br/>
	Link: $link
	<br><br>
	<b>$name $surname ($department)</b> <a href ='https://find.siemens.cloud/search?text=" . $email . "'>SCD Lookup</a>
	<br><br>
	
	<br>$selfOrgCode
";
MailManager::sendMail($subject, $body, 'internal');
?>
<html>
<head>
    <title>OneX</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
    <link rel="icon" href="/favicon.ico" type="image/x-icon">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"/>
    <meta charset="utf-8">

    <link rel="shortcut icon" href="/favicon.ico"/>
    <link href="../../css/semantic.min.css" rel="stylesheet"/><style>
        .ui.grid>.row>.column {
            background: rgba(255, 255, 255, 0.2) !important;
            border-radius: 16px;
            box-shadow: 0 4px 30px rgb(0 0 0 / 10%) !important;
            backdrop-filter: blur(5px) !important;
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            margin: 9px !important;
            margin-top: 0 !important;
            width: 23% !important;
            min-height: 212.66px !important;
        }
        .ui.grid>.row>.column:hover{
            background: #009999bf !important;
        }
        @media only screen and (min-width: 768px) and (max-width: 991px) {
            .ui[class*="four column"].doubling.grid>.row>.column{
                width: 40% !important;
                margin-bottom: 16px !important;
            }
        }
        @media only screen and (max-width: 767px) {
            .ui[class*="four column"].doubling:not(.stackable).grid>.row>.column{
                width: 40% !important;
                margin: 0 9px 16px 9px !important;
            }
        }
        .column:hover {
            opacity: 1 !important;
            /* css standard */
            filter: alpha(opacity=100);
            /* internet explorer */
        }

        body {
            background: linear-gradient(180deg, #000028 0%, #009999 100%) fixed;
        }

        .footer {
            padding: 0;
            overflow-x: hidden;
            background: #ffffff;
            font-family: 'Roboto', 'Helvetica Neue', Arial, Helvetica, sans-serif;
            font-size: 14px;
            line-height: 1.4285em;
            color: rgba(0, 0, 0, 0.87);
            margin: 25px auto;
            text-align: center;
            width: 185px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<div class="ui secondary segment">
    <a href="/">
        <svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" width="180px" height="40px" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" viewBox="0 0 210 50">
            <defs>
                <style type="text/css">
                    .fil0 {
                        fill: #009999
                    }
                </style>
            </defs>
            <g id="Ebene_x0020_1">
                <metadata id="CorelCorpID_0Corel-Layer"></metadata>
                <path class="fil0" d="M200.121 10.3466l0 5.8289c-3.0198,-1.14 -5.7084,-1.7164 -8.0615,-1.7164 -1.3938,0 -2.5037,0.2581 -3.3382,0.7571 -0.8346,0.5033 -1.2605,1.1228 -1.2605,1.8541 0,0.9722 0.9421,1.8368 2.8392,2.6112l5.4805 2.6671c4.4309,2.1122 6.6291,4.9169 6.6291,8.4401 0,2.9295 -1.1658,5.2654 -3.5189,6.9947 -2.3359,1.7466 -5.4805,2.6112 -9.3951,2.6112 -1.8068,0 -3.4285,-0.0774 -4.8696,-0.2409 -1.4411,-0.1548 -3.0973,-0.4732 -4.9342,-0.9292l0 -6.0999c3.3683,1.14 6.4355,1.7164 9.1972,1.7164 3.2952,0 4.9342,-0.955 4.9342,-2.8822 0,-0.9593 -0.6711,-1.7336 -2.0347,-2.3402l-6.0871 -2.594c-2.2455,-1.0152 -3.9146,-2.2455 -5.0073,-3.7038 -1.0754,-1.4712 -1.6218,-3.1575 -1.6218,-5.0847 0,-2.6973 1.1357,-4.8697 3.3812,-6.5216 2.2628,-1.639 5.2655,-2.4606 8.9994,-2.4606 1.2131,0 2.6112,0.1075 4.1599,0.3054 1.5615,0.2108 3.0628,0.4689 4.5082,0.7873z"></path>
                <path class="fil0" d="M27.7222 10.3466l0 5.8289c-3.0199,-1.14 -5.7042,-1.7164 -8.0573,-1.7164 -1.3981,0 -2.5036,0.2581 -3.3382,0.7571 -0.8345,0.5033 -1.2604,1.1228 -1.2604,1.8541 0,0.9722 0.955,1.8368 2.8521,2.6112l5.4805 2.6671c4.4136,2.1122 6.6162,4.9169 6.6162,8.4401 0,2.9295 -1.1701,5.2654 -3.506,6.9947 -2.3531,1.7466 -5.4805,2.6112 -9.408,2.6112 -1.8068,0 -3.4329,-0.0774 -4.874,-0.2409 -1.4411,-0.1548 -3.0801,-0.4732 -4.9298,-0.9292l0 -6.0999c3.3812,1.14 6.4484,1.7164 9.1929,1.7164 3.2952,0 4.9342,-0.955 4.9342,-2.8822 0,-0.9593 -0.6668,-1.7336 -2.0176,-2.3402l-6.087 -2.594c-2.2628,-1.0152 -3.9319,-2.2455 -5.0073,-3.7038 -1.0927,-1.4712 -1.6261,-3.1575 -1.6261,-5.0847 0,-2.6973 1.1271,-4.8697 3.3855,-6.5216 2.2456,-1.639 5.2525,-2.4606 8.9865,-2.4606 1.226,0 2.6069,0.1075 4.1727,0.3054 1.5487,0.2108 3.05,0.4689 4.4911,0.7873z"></path>
                <polygon class="fil0" points="34.0028,9.8002 42.9291,9.8002 42.9291,39.8483 34.0028,39.8483 "></polygon>
                <polygon class="fil0" points="71.6866,9.8002 71.6866,15.3539 58.4241,15.3539 58.4241,22.0173 69.9272,22.0173 69.9272,27.0246 58.4241,27.0246 58.4241,34.0194 71.9576,34.0194 71.9576,39.8483 49.8335,39.8483 49.8335,9.8002 "></polygon>
                <polygon class="fil0" points="113.358,9.8002 113.358,39.8483 105.025,39.8483 105.025,20.0299 96.3789,40.1236 91.234,40.1236 82.9186,20.0299 82.9186,39.8483 76.8918,39.8483 76.8918,9.8002 87.7882,9.8002 95.226,28.1947 103.008,9.8002 "></polygon>
                <polygon class="fil0" points="142.103,9.8002 142.103,15.3539 128.913,15.3539 128.913,22.0173 140.416,22.0173 140.416,27.0246 128.913,27.0246 128.913,34.0194 142.374,34.0194 142.374,39.8483 120.25,39.8483 120.25,9.8002 "></polygon>
                <polygon class="fil0" points="173.424,9.8002 173.424,39.8483 163.956,39.8483 153.331,20.5762 153.331,39.8483 147.308,39.8483 147.308,9.8002 157.052,9.8002 167.402,28.7411 167.402,9.8002 "></polygon>
            </g>
        </svg>
    </a>
</div>
<br>
<div class='ui two column centered grid'></div>
<div class="ui text container">
    <div class="ui raised very padded text container segment">
        User IP: <?php echo SharedManager::getUserIP(); ?><br>
        Unfortunately, according to our security check it seems that you are not allowed to use OneX.
        A permission request should be sent to us by your line manager in order to use OneX.<br>
        <a href='mailto:<?= $contactEmail ?>?subject=OneX Access Request over Web'>Send an e-mail to us </a>

    </div>
</div>
<div class="ui raised very padded text container segment" style="padding-bottom: 0px;">
    <b><p style="text-align:center;">OneX</p>
    <div class="footer">
        <a href="mailto:<?= $contactEmail ?>?subject=OneX Not Allowed Page">
            UI Design &amp; Programming
            <b><?= $selfOrgCode ?></b>
        </a>
    </div>
</div>
</body>
</html>

			