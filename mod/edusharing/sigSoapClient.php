<?php
// This file is part of edu-sharing created by metaVentis GmbH — http://metaventis.com
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.



/**
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusharing_sig_soap_client extends SoapClient {
    
    private $appProperties;
    
    public function __construct($wsdl, $options = array()) {
        $this -> mod_edusharing_set_app_properties();
        parent::__construct($wsdl, $options);
        $this -> mod_edusharing_set_soap_headers();
    }
    
    private function mod_edusharing_set_soap_headers() {
        try {
            $timestamp = round(microtime(true) * 1000);               
            $signData = $this -> mod_edusharing_get_app_properties() -> appid . $timestamp;
            $priv_key = $this -> mod_edusharing_get_app_properties() -> private_key;      
            $pkeyid = openssl_get_privatekey($priv_key);      
            openssl_sign($signData, $signature, $pkeyid);
            $signature = base64_encode($signature);
            openssl_free_key($pkeyid);
            $headers = array();
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'appId', $this -> mod_edusharing_get_app_properties() -> appid);
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'timestamp', $timestamp); 
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signature', $signature); 
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signed', $signData); 
            parent::__setSoapHeaders($headers);
        } catch (Exception $e) {
            throw new Exception('Could not set soap headers - ' . $e -> getMessage());
        }
    }
        
    public function mod_edusharing_set_app_properties() {
        $this -> appProperties = json_decode(get_config('edusharing', 'appProperties'));
    }
    
    public function mod_edusharing_get_app_properties() {
        if(empty($this -> appProperties))
            throw new Exception('No appProperties found');
        return $this -> appProperties;
    }

}
