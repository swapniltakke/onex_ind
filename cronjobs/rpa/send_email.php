<?php

require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronManagers.php';

/*
 * can call via following url:
 * /cronjobs/rpa/send_email.php?mm_action=mm_action&subject=$SUBJECT&body=$body&mailKey=$MAIL_KEY&module=$MODULE_ID&tos=$TOS&ccs=$CCS
 *
 * */
if ($_REQUEST['mm_action']) {
    $attachments = explode(';', $_REQUEST['attachments']);
    $tos = explode(';', $_REQUEST['tos']);
    $ccs = explode(';', $_REQUEST['ccs']);

    foreach ($tos as $to)
        if($to && !str_ends_with($to, 'siemens.com'))
            returnHttpResponse(400, "Emails should end with 'siemens.com'");

    foreach ($ccs as $cc)
        if($cc && !str_ends_with($cc, 'siemens.com'))
            returnHttpResponse(400, "Emails should end with 'siemens.com'");

    $bodyContent = CronFunctions::getOneXMailContent($_REQUEST['body'], $_REQUEST['subject']);
    if($_REQUEST['mm_action'] == "withoutmysql")
        CronMailManager::sendMailWithoutMysql($_REQUEST['subject'],$bodyContent, $tos, $ccs,$attachments);
    else
        CronMailManager::sendMail($_REQUEST['subject'], $bodyContent, $_REQUEST['mailKey'], $attachments, $tos, $ccs);
}

