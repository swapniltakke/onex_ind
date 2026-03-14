<?php
error_reporting(E_ERROR | E_PARSE);

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/PHPMailer/src/Exception.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/PHPMailer/src/PHPMailer.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/PHPMailer/src/SMTP.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/shared.php';
require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronManagers.php';

class CronMailManager
{
    private static $mail;
    private static object $mailSettings;

    static function removeDeactiveUsers(array $users): array{
        require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronDbManager.php';
        $query = "SELECT email FROM users WHERE status=0 AND email IN(:emails)";
        $result = CronDbManager::fetchQueryData('php_auth', $query, [":emails" => $users])['data'];
        if(!empty($result)){
            return array_column($result, 'email') ?? [];
        }
        return [];
    }

    static function sendMail($subject, $body, $mailKey = '', $attachments = array(), $tos = array(), $ccs = array(), $bccs = array())
    {
        try {
            self::setMail();
            $values = self::getMailAddresses($mailKey)[0];
            foreach (explode(";", $values["tos"] ?? "") as $x) $tos[] = $x;
            foreach (explode(";", $values["ccs"] ?? "") as $x) $ccs[] = $x;
            foreach (explode(";", $values["bccs"] ?? "") as $x) $bccs[] = $x;

            $tos = array_diff($tos, self::removeDeactiveUsers($tos));
            $ccs = array_diff($ccs, self::removeDeactiveUsers($ccs));

            foreach ($tos as $to) if (!empty($to)) self::$mail->addAddress($to);
            foreach ($ccs as $cc) if (!empty($cc)) self::$mail->addCC($cc);
            foreach ($bccs as $bcc) if (!empty($bcc)) self::$mail->addBCC($bcc);
            foreach ($attachments as $attachment) if (!empty($attachment)) self::$mail->addAttachment($attachment);
            self::$mail->Subject = $subject;
            self::$mail->Body = $body;
            if (self::$mail->send()) {
                self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), 1);
            }
        } catch (Exception $e) {
            $exception_message = "Mail Exception message : " . $e->getMessage();
            self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), $exception_message);
            throw new Error($exception_message);
        }
    }

    static function sendHiddenMail($subject, $body, $mailKey = '', $attachments = array(), $tos = array(), $ccs = array(), $bccs=array()): void
    {
        try{
            self::setMail();
            $values = self::getMailAddresses($mailKey)[0];
            foreach (explode(";", $values["tos"] ?? "") as $x) $tos[] = $x;
            foreach (explode(";", $values["ccs"] ?? "") as $x) $ccs[] = $x;
            foreach (explode(";", $values["bccs"] ?? "") as $x) $bccs[] = $x;

            foreach ($tos as $to) if (!empty($to)) self::$mail->addBCC($to, '');
            foreach ($ccs as $cc) if (!empty($cc)) self::$mail->addBCC($cc, '');
            foreach ($bccs as $bcc) if (!empty($bcc)) self::$mail->addBCC($bcc, '');
            foreach ($attachments as $attachment) if (!empty($attachment)) self::$mail->addAttachment($attachment);

            self::$mail->Subject = $subject;
            self::$mail->Body = $body;
            // self::$mail->addReplyTo("groupdgt.tr@internal.siemens.com");
            if (self::$mail->send()) {
                self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), 1);
            }

        } catch (Exception $e) {
            $exception_message = "Mail Exception message : " . $e->getMessage();
            self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), $exception_message);
            throw new Error($exception_message);
        }
    }


    static function setEnv(){
        require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/composer/vendor/autoload.php";

        $dotenv = Dotenv\Dotenv::createImmutable($_SERVER["DOCUMENT_ROOT"] . "/shared/PHPMailer");
        $dotenv->load();
        self::$mailSettings = new stdClass();
        self::$mailSettings->Host = $_ENV["HOST"];
        self::$mailSettings->Username = $_ENV["USERNAME"];
        self::$mailSettings->Password = $_ENV["PASSWORD"];
        self::$mailSettings->SMTPSecure = $_ENV["SMTP_SECURE"];
        self::$mailSettings->Port = $_ENV["PORT"];
        self::$mailSettings->CharSet =$_ENV["CHARSET"];
        self::$mailSettings->Sender = $_ENV["SENDER"];
        self::$mailSettings->WordWrap = $_ENV["WORD_WRAP"];

        return self::$mailSettings;
    }

    static function setMail()
    {
        try {
            self::setEnv();
            self::$mail = new PHPMailer(true);
            self::$mail->isSMTP();
            self::$mail->Host = self::$mailSettings->Host;
            self::$mail->SMTPAuth = true;
            self::$mail->Username = self::$mailSettings->Username;
            self::$mail->Password = self::$mailSettings->Password;
            self::$mail->SMTPSecure = self::$mailSettings->SMTPSecure;
            self::$mail->Port = self::$mailSettings->Port;
            self::$mail->CharSet = self::$mailSettings->CharSet;
            self::$mail->Sender = self::$mailSettings->Sender;
            self::$mail->isHTML();
            self::$mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );
            self::$mail->WordWrap = self::$mailSettings->WordWrap;
        } catch (Exception $e) {
            $exception_message = "Mail Exception message1 : " . $e->getMessage() . " " . $e->getTraceAsString();
            throw new Error($exception_message);
        }
    }

    static function getMailAddresses($mailKey)
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronDbManager.php';
        $query = "SELECT tos,ccs,bccs FROM mail_users WHERE mailKey=:mailKey LIMIT 1";
        return CronDbManager::fetchQueryData('php_auth', $query, [":mailKey" => $mailKey])["data"];
    }

    static function MailLog($subject, $body, $tos, $ccs, $bccs, $silent)
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/cronjobs/CronDbManager.php';

        $subject = CronDbManager::Filter($subject);
        $body = CronDbManager::Filter($body);
        $tos = CronDbManager::Filter($tos);
        $ccs = CronDbManager::Filter($ccs);
        $bccs = CronDbManager::Filter($bccs);
        $silent = CronDbManager::Filter($silent);
        $sql = "INSERT INTO mails_sent (subject, body, address_tos, address_ccs, address_bccs, from_mail, from_name, silent)
                    VALUES (:subject, :body, :tos, :ccs, :bccs, 'onex_ind.in@siemens.com','SI EA O AIS THA OPEX DGT', :silent);";

        CronDbManager::fetchQuery('logs', $sql, [
            ":subject" => $subject,
            ":body" => $body,
            ":tos" => $tos,
            ":ccs" => $ccs,
            ":bccs" => $bccs,
            ":silent" => $silent
            ]
        );
    }

    static function ExceptionMail($filePath, $line, $message)
    {
        $request_time = date('d.m.Y H:i:s');
        $mail_body = 'Aşağıdaki Dosyada Exceptiona Düşüldü. <br><br>
                        <strong>İstek Adresi: </strong> ' . $filePath . ', Line:' . $line . '<br>
                        <strong>IP: </strong>' . SharedManager::$user_ip . '<br>
                        <strong>Tarih: </strong> ' . $request_time . '<br>
                        <strong>Mail: </strong>' . SharedManager::$email . '<br>
                        <strong>GID: </strong>' . SharedManager::$gid . '<br>
                        <strong>Exception Message: </strong> ' . $message . '<br>';
        self::sendMailWithoutMysql("Exception -  $filePath Line:" . $line, $mail_body, ["groupdgt.tr@internal.siemens.com"]);
    }

    static function sendMailWithoutMysql($subject, $body, $tos = array(), $ccs = array(), $attachments = array())
    {
        try {
            self::setMail();
            foreach (explode(";", $values["tos"] ?? "") as $x) $tos[] = $x;
            foreach (explode(";", $values["ccs"] ?? "") as $x) $ccs[] = $x;

            foreach ($tos as $to) if (!empty($to)) self::$mail->addAddress($to);
            foreach ($ccs as $cc) if (!empty($cc)) self::$mail->addCC($cc);
            foreach ($attachments as $attachment) if (!empty($attachment)) self::$mail->addAttachment($attachment);
            self::$mail->Subject = $subject;
            self::$mail->Body = $body;
            if (self::$mail->send()) {
                echo "success";
            }
        } catch (Exception $e) {
            throw new Error($e->getMessage());
        }
    }
}

?>
