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
    private $iosKeyFile, $iosCertificateFile, $iosKeyPass, $iosMode;

    const TOKEN_TYPE_GCM = 1;
    const TOKEN_TYPE_APS = 2;

    /**
     *  Constructor. Load push settings and init database.
     */
    public function __construct()
    {
        $config = parse_ini_file("./config/config.ini", true);

        $this->androidApiKey = $config["push"]["android-api-key"];

        $this->iosCertificateFile = $config["push"]["ios-cert-file"];
        $this->iosKeyFile = $config["push"]["ios-key-file"];
        $this->iosKeyPass = $config["push"]["ios-key-pass"];
        $this->iosMode = ($config["push"]["ios-mode"] == "production") ? APN_PRODUCTION : APN_SANDBOX;

        $this->db = Database::getInstance();
    }

    /**
     * Check if token exists in database
     * @param $token
     * @param $type
     * @return bool
     */
    private function isTokenExists($token, $type)
    {
        $query = $this->db->_db->prepare("SELECT COUNT(1) FROM `tokens` WHERE `key` = :key AND `type` = :type LIMIT 1");
        $query->execute( array( "key" => $token,
                                "type" => (int) $type )
                        );
        $result = $query->fetchColumn();

        return (bool) $result;
    }


    /**
     * Add new token to database
     * @param $token
     * @param $type
     * @return bool
     * @throws AppException
     */
    public function registerNewToken($token, $type)
    {
        if ($type != self::TOKEN_TYPE_APS || $type != self::TOKEN_TYPE_GCM)
        {
            throw new AppException('Invalid token type given');
        }

        if ($this->isTokenExists($token, $type))
        {
            return false;
        }

        $query = $this->db->_db->prepare("INSERT INTO `tokens` SET `key` = :key, `type` = :type");
        $query->execute( array( "key" => $token,
                                "type" => (int) $type )
                        );

        return true;
    }

    /**
     * @param $message
     * @param $elementId
     * @throws AppException
     */
    public function sendData($message, $elementId)
    {
        $gcmTokens = $this->getTokens(self::TOKEN_TYPE_GCM);
        $apsTokens = $this->getTokens(self::TOKEN_TYPE_APS);

        $this->sendDataToGCM($gcmTokens, $message, $elementId);
        $this->sendDataToAPS($apsTokens, $message, $elementId);
    }

    /**
     * Sends push to Apple Push Notification Service (for iOS apps)
     * @param $tokens
     * @param $message
     * @param $elementId
     * @throws AppException
     */
    private function sendDataToAPS($tokens, $message, $elementId)
    {
        if (sizeof($tokens) == 0) return;

        if (!file_exists($this->iosCertificateFile)) {
           throw new AppException("iOS certificate file not exists or incorrect path");
        }

        if (!file_exists($this->iosKeyFile)) {
            throw new AppException("iOS key file not exists or incorrect path");
        }

        $apn = apn_init();
        apn_set_array($apn, array(
            'certificate' => $this->iosCertificateFile,
            'private_key' => $this->iosKeyFile,
            'private_key_pass' => $this->iosKeyPass,
            'tokens' => $tokens,
            'mode' => $this->iosMode
        ));

        $payload = apn_payload_init();
        apn_payload_set_array($payload, array(
            'body' => substr($message, 0, 65) . "…"
        ));

        apn_payload_add_custom_property($payload, 'i', $elementId);

        $error = NULL;
        $errorCode = 0;

        if(apn_connect($apn, $error, $errorCode)) {
            if(!apn_send($apn, $payload, $error, $errorCode)) {
                throw new AppException('Could not sent push notification: ' . $error);
            }
        } else {
            throw new AppException('Could not connected to Apple Push Notification Service: ' . $error);
        }

        apn_close($apn);
        apn_payload_free($payload);
        apn_free($apn);
    }

    /**
     * Sends push via Google Cloud Messaging (for Android apps)
     * @param $tokens
     * @param $message
     * @param $elementId
     * @throws AppException
     */
    private function sendDataToGCM($tokens, $message, $elementId)
    {
        if (sizeof($tokens) == 0) return;

        if (strlen($this->androidApiKey) == 0)
        {
            throw new AppException("Google API key is not specified");
        }

        $headers = array
        (
            'Authorization: key=' . $this->androidApiKey,
            'Content-Type: application/json'
        );

        $payloadMessage = array
        (
            'message' 		=> $message,
            'title'			=> $message,
            'subtitle'		=> '',
            'tickerText'	=> '',
            'vibrate'	=> 1,
            'sound'		=> 1,
            'obj' => $elementId
        );

        $payload = array
        (
            'registration_ids' => $tokens,
            'data' => $payloadMessage
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_exec($ch);

    }

    /**
     * Request token with some type from DB
     * @param $type
     * @return array
     */
    private function getTokens($type)
    {
        $query = $this->db->_db->prepare("SELECT `key` FROM `tokens` WHERE `type` = :type");
        $query->execute( array("type" => $type ) );
        $data = Array();

        while ($row = $query->fetch(PDO::FETCH_ASSOC))
        {
            $data[] = $row['key'];
        }

        return $data;
    }


}