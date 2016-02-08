<?php

class sigSoapClient extends SoapClient {
    
    private $homeConf;
    
    
    public function __construct($wsdl, $options = array(), $homeConf) {
        $this -> setHomeConf($homeConf);
        parent::__construct($wsdl, $options);
        $this -> setSoapHeaders();
    }
    
    private function setSoapHeaders() {
        try {
            $timestamp = round(microtime(true) * 1000);               
            $signData = $this -> getHomeConf() -> prop_array['appid'] . $timestamp;
            $priv_key = $this -> getHomeConf() -> prop_array['private_key'];       
            $pkeyid = openssl_get_privatekey($priv_key);      
            openssl_sign($signData, $signature, $pkeyid);
            $signature = base64_encode($signature);
            openssl_free_key($pkeyid);
            $headers = array();
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'appId', $this -> getHomeConf() -> prop_array['appid']);
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'timestamp', $timestamp); 
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signature', $signature); 
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signed', $signData); 
            parent::__setSoapHeaders($headers);
        } catch (Exception $e) {
            throw new Exception('Could not set soap headers - ' . $e -> getMessage());
        }
    }
    
    
    /*
    
    public function __doRequest($request, $location, $action, $version, $one_way = 0) {
        $this -> updateSoapHeaders($request);    
        return parent::__doRequest($request, $location, $action, $version, $one_way);
    }
    
    private function updateSoapHeaders(&$request) {
        $timestamp = round(microtime(true) * 1000);               
        $signData = $this -> getSoapRequestBody($request) . $timestamp;
        
        //var_dump($signData);die();
        
        $priv_key = $this -> getHomeConf() -> prop_array['private_key'];       
        $pkeyid = openssl_get_privatekey($priv_key);      
        openssl_sign($signData, $signature, $pkeyid);
        $signature = base64_encode($signature);
        openssl_free_key($pkeyid);   
        $arrSearch = array('[[[timestamp]]]', '[[[signature]]]');
        $arrReplace = array($timestamp, $signature);
        $request = str_replace($arrSearch, $arrReplace, $request);
    }
    
    private function setSoapHeaders() {
        $headers[] = new SOAPHeader('http://usage2.webservices.edu_sharing.org', 'appId', $this -> getHomeConf() -> prop_array['appid']);
        $headers[] = new SOAPHeader('http://usage2.webservices.edu_sharing.org', 'timestamp', '[[[timestamp]]]'); 
        $headers[] = new SOAPHeader('http://usage2.webservices.edu_sharing.org', 'signature', '[[[signature]]]'); 
        $this -> __setSoapHeaders($headers);    
    }
    
    private function getSoapRequestBody($request) {
        preg_match('/<SOAP-ENV:Body>(.*?)<\/SOAP-ENV:Body>/s', $request, $matches);
        return $matches[0];
    }
     
     */
    
    public function setHomeConf($homeConf) {
        $this -> homeConf = $homeConf;
    }
    
    public function getHomeConf() {
        if(empty($this -> homeConf))
            throw new Exception('No homeConf found');
        return $this -> homeConf;
    }

}
