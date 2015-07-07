<?php

define("APN_PRODUCTION", 1);
define("APN_SANDBOX", 2);

function apn_init(){return "";}
function apn_set_array($handler, $config) {}
function apn_payload_init(){return"";}
function apn_payload_set_array($payload, $params){}
function apn_payload_add_custom_property($payload, $name, $value){}
function apn_connect($apn, &$error, &$errorCode){return (bool) $apn . $error . $errorCode;}
function apn_send($apn, $payload, &$error, &$errorCode){return (bool) $apn . $payload . $error . $errorCode;}
function apn_close($apn){}
function apn_payload_free($payload){}
function apn_free($apn){}
