<?php

session_start();
$user=$_SESSION['username'];
$pass=$_SESSION['pass'];
include 'DatabaseConfig.php';

?>
<html>
<body><a href="https://login.microsoftonline.com/common/oauth2/v2.0/authorize?client_id=4765445b-32c6-49b0-83e6-1d93765276ca&redirect_uri=https%3A%2F%2Fwww.office.com%2Flandingv2&response_type=code%20id_token&scope=openid%20profile%20https%3A%2F%2Fwww.office.com%2Fv2%2FOfficeHome.All&response_mode=form_post&nonce=638605255253533359.MDUyMTNkYWUtYWM4My00YzA3LTlmMjctN2M0NDZiOGIzZjM3NjhiNjMwNTQtM2FiYy00N2E1LWE1ZjMtNjEyNzczNzI4YTA4&ui_locales=en-US&mkt=en-US&client-request-id=c1f663df-c7f1-4525-9c6e-ee8178b69aa4&state=7KjHN-mmAmO5lmxht-cunHD2Qgmos88GS3j5aeDfovjlbDdCoZXWzKNQWIwfZ9a6YEJxBW-k3LXclF-gVwfFvu9o62e9ZK54WioqMmPUttQgtEB0zwvWtFSW97IRRdoQtzzKdSr_BxzORo02chJnYmpoVDym9aMm60N0dkkMgl8jmUBG-wxLQrF5ENUKnE97m68kXYEfCochc-TxyVW2Z-PdANEPrUeNr9WqIMkVThYtmvkVPSL0hzOPk0arwj7b6d7YsbgXFvjU_wP7FoHY5A&x-client-SKU=ID_NET8_0&x-client-ver=7.5.1.0">Microsoft</a></body>
</html>