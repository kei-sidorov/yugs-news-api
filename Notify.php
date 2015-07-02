<?php
/**
 * Created by PhpStorm.
 * User: kirillsidorov
 * Date: 02.07.15
 * Time: 15:31
 */

class Notify {

    private $db;
    private $androidApiKey = "";
    private $iosKeyFile, $iosCertificateFile, $iosKeyPass;

    /**
     *  Constructor. Load push settings and init database.
     */
    public function __construct()
    {
        $config = parse_ini_file("config.ini", true);

        $this->androidApiKey = $config["push"]["android-api-key"];

        $this->iosCertificateFile = $config["push"]["ios-cert-file"];
        $this->iosKeyFile = $config["push"]["ios-key-file"];
        $this->iosKeyPass = $config["push"]["ios-key-pass"];

        $this->db = db::getInstance();
    }

    /**
     * Check if token exists in database
     * @param $token
     * @return bool
     */
    private function isTokenExists($token)
    {
        $query = $this->db->db->prepare("SELECT COUNT(1) FROM `tokens` WHERE `key` = :key LIMIT 1");
        $query->execute(array("key" => $token));
        $result = $query->fetchColumn();

        return (bool) $result;
    }


    /**
     * Add new token to database
     * @param $token
     * @return bool
     */
    public function registerNewToken($token)
    {
        if ($this->isTokenExists($token))
        {
            return false;
        }

        $query = $this->db->db->prepare("INSERT INTO `tokens` SET `key` = :key");
        $query->execute(array("key" => $token));

        return true;
    }

    /**
     * @param $message
     * @param $typeId
     * @param $elementId
     */
    public function sendData($message, $typeId, $elementId) {
        $gcmTokens = [];
        $apsTokens = [];

        foreach($tokens as $token) {
            if (preg_match('/^[a-f0-9]{64}$/i', $token)) {
                $apsTokens[] = $token;
            }else{
                $gcmTokens[] = $token;
            }
        }

        $this->sendDataToGCM($gcmTokens, $message, $typeId, $elementId);
        $this->sendDataToAPS($apsTokens, $message, $typeId, $elementId);
    }

    /**
     * Sends push to Apple Push Service
     * @param $tokens
     * @param $message
     * @param $typeId
     * @param $elementId
     * @throws AppException
     */
    private function sendDataToAPS($tokens, $message, $typeId, $elementId) {
        $apn = apn_init();
        apn_set_array($apn, array(
            'certificate' => $this->iosCertificateFile,
            'private_key' => $this->iosKeyFile,
            'private_key_pass' => $this->iosKeyPass,
            'tokens' => $tokens,
            'mode' => APN_PRODUCTION
        ));

        $payload = apn_payload_init();
        apn_payload_set_array($payload, array(
            'body' => substr($message, 0, 65) . "…"
        ));

        apn_payload_add_custom_property($payload, 't', $typeId);
        apn_payload_add_custom_property($payload, 'i', $elementId);

        $error = NULL;
        $errorCode = 0;

        if(apn_connect($apn, $error, $errorCode)) {
            if(!apn_send($apn, $payload, $error, $errorCode)) {
                throw new AppException('Could not sent push notification: ' . $error);
            }
        } else {
            throw new AppException('Could not connected to Apple Push Notification Servece: ' . $error);
        }

        apn_close($apn);
        apn_payload_free($payload);
        apn_free($apn);
    }


}