<?php
error_reporting(E_ERROR | E_PARSE);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once dirname(__FILE__) . '/PHPMailer/src/Exception.php';
require_once dirname(__FILE__) . '/PHPMailer/src/PHPMailer.php';
require_once dirname(__FILE__) . '/PHPMailer/src/SMTP.php';
require_once dirname(__FILE__) . '/shared.php';

class MailManager
{
    private static object $mail;
    public static object $mailSettings;

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
            self::$mail->WordWrap = self::$mailSettings->WordWrap;
        } catch (Throwable $e) {
            echo "SetMail Exception message : " . $e->getMessage() . " " . $e->getTraceAsString();
            die();
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
        self::$mailSettings->FROM_NAME = $_ENV["FROM_NAME"];

        return self::$mailSettings;
    }

    static function getMailAddresses($mailKey)
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/DbManager.php';
        $query = "SELECT tos, ccs, bccs FROM mail_users WHERE mailKey=:p1 LIMIT 1";
        $mailUsers = DbManager::fetchPDOQueryData('php_auth', $query, [":p1" => $mailKey])["data"];
        if(count($mailUsers) === 0)
            Throw new Error("Mail users for key '$mailUsers' is not found");
        return $mailUsers;
    }

    static function sendMail($subject, $body, $mailKey = '', $attachments=[], $tos=[], $ccs=[], $bccs=[])
    {
        try {
            self::setMail();
            $values = self::getMailAddresses($mailKey)[0];
            foreach (explode(";", $values["tos"] ?? "") as $x) $tos[] = $x;
            foreach (explode(";", $values["ccs"] ?? "") as $x) $ccs[] = $x;
            foreach (explode(";", $values["bccs"] ?? "") as $x) $bccs[] = $x;
            foreach ($tos as $to) if (!empty($to)) self::$mail->addAddress($to);
            foreach ($ccs as $cc) if (!empty($cc)) self::$mail->addCC($cc);
            foreach ($bccs as $bcc) if (!empty($bcc)) self::$mail->addBCC($bcc);
            foreach ($attachments as $attachment) if (!empty($attachment)) self::$mail->addAttachment($attachment);
            self::$mail->Subject = $subject;
            self::$mail->Body = $body;
            if (self::$mail->send()) {
                self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), 1);
            }
        } catch (Throwable $e) {
            $exception_message = "Mail Exception message : " . $e->getMessage();
            self::MailLog($subject, $body, implode(';', $tos), implode(';', $ccs), implode(';', $bccs), $exception_message);
            die();
        }
    }

    static function MailLog($subject, $body, $tos, $ccs, $bccs, $silent)
    {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/shared/DbManager.php';
        $mailLogQuery = "INSERT INTO mails_sent (subject, body, address_tos, address_ccs, address_bccs, from_mail, from_name, silent)
                    VALUES (:subject, :body, :tos, :ccs, :bccs, :from_mail, :from_name, :silent);";
        $queryParams = [
            ":subject" => $subject,
            ":body" => $body,
            ":tos" => $tos,
            ":ccs" => $ccs,
            ":bccs" => $bccs,
            ":from_mail" => self::$mailSettings->SENDER,
            ':from_name' => self::$mailSettings->FROM_NAME,
            ":silent" => $silent
        ];
        DbManager::fetchPDOQuery('logs', $mailLogQuery, $queryParams);
    }
}

?>