<?php
/*
 * DO NOT INCLUDE ANYTHING
*/

require_once $_SERVER["DOCUMENT_ROOT"] . "/shared/shared.php";

class BaseManager {
    private static function getCredentialsPath(){
        $_USER_IP = getUserIP();
        if(in_array($_USER_IP, ['127.0.0.1', '::1']))
            return "C:\\DBCredentials-THA.dat";
        else
            return $_SERVER['DOCUMENT_ROOT'] . '/../../DBCredentials.dat';
    }

    public static function getCredentials($dbname)
    {
        try {
            $credentialsPath = self::getCredentialsPath();

            $ciphertext = file_get_contents($credentialsPath);
			
            $secret_key = getenv("DB_CREDENTIALS_THA");
			
            $c = base64_decode($ciphertext);
            $key = openssl_digest($secret_key, 'SHA256', TRUE);
            $ivlen = openssl_cipher_iv_length('AES-128-CBC');
            $iv = substr($c, 0, $ivlen);
            $hmac = substr($c, $ivlen, $sha2len = 32);
            $ciphertext_raw = substr($c, $ivlen + $sha2len);
            $original_plaintext = openssl_decrypt($ciphertext_raw, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
            if (hash_equals($hmac, $calcmac))
                return json_decode($original_plaintext, true, 512, JSON_THROW_ON_ERROR)[$dbname];
            else
                throw new Error("Timing attack safe string comparison is not true");
        } catch (Exception $e) {
            error_reporting(E_ERROR | E_PARSE);
            throw new Error("DbManager getCredentials Error, {$e->getMessage()}");
        }
    }

    /**
     * @param $data
     * @return string|array
     */
    public static function Filter($data): string|array
    {
        $array = [
            '&quot;' => '"',
            '&Ouml;' => 'Ö',
            '&ouml;' => 'ö',
            '&Uuml;' => 'Ü',
            '&uuml;' => 'ü',
            '&ccedil;' => 'ç',
            '&amp;' => '&',
            '&amp;Ccedil;' => 'Ç',
            '&Ccedil;' => 'Ç',
            '&acute;' => "'",
            '&#039;' => "'"
        ];
        if (is_array($data)) {
            return array_map(static function ($value) use ($array) {
                return str_replace(array_keys($array), array_values($array), htmlentities($value));
            }, $data);
        }
        if (!isset($data)) $data = '';
        return str_replace(array_keys($array), array_values($array), htmlentities($data));
    }
}