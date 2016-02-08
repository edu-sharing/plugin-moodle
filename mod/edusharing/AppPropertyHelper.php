<?php

class AppPropertyHelper {
    
    public function __construct() {

    }

    public function addSslKeypairToHomeConfig() {
        $sslKeypair = $this -> getSslKeypair();
        $xml = simplexml_load_file(dirname(__FILE__) . '/conf/esmain/homeApplication.properties.xml');
        $pubKey = $xml->addChild("entry");
        $pubKey -> addAttribute("key", "public_key");
        $pubKey[0] = $sslKeypair['publicKey'];
        $privateKey = $xml->addChild("entry");
        $privateKey -> addAttribute("key", "private_key");
        $privateKey[0] = $sslKeypair['privateKey'];
        $xml->asXML(dirname(__FILE__) . '/conf/esmain/homeApplication.properties.xml');
    }
    
    public function getSslKeypair() {
        $sslKeypair = array();
        $res=openssl_pkey_new();
        openssl_pkey_export($res, $privatekey);
        $publickey=openssl_pkey_get_details($res);
        $publickey=$publickey["key"];
        $sslKeypair['privateKey'] = $privatekey;
        $sslKeypair['publicKey'] = $publickey;
        return $sslKeypair;
    }
    
    public function addSignatureRedirector() {
        $xml = simplexml_load_file(dirname(__FILE__) . '/conf/esmain/homeApplication.properties.xml');
        $signatureRedirector = $xml->addChild("entry");
        $signatureRedirector -> addAttribute("key", "signatureRedirector");
        $signatureRedirector[0] = $this -> getSignatureRedirector();
        $xml->asXML(dirname(__FILE__) . '/conf/esmain/homeApplication.properties.xml');
    }

    public function getSignatureRedirector() {
        global $CFG;
        return $CFG->wwwroot . '/filter/edusharing/signatureRedirector.php';
    }
    
}
