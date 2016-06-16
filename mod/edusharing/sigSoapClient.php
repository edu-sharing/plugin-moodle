<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Extend PHP SoapClient with some header information
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Extend PHP SoapClient with some header information
 *
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class mod_edusharing_sig_soap_client extends SoapClient {

    /**
     * @var array $appproperties
     */
    private $appproperties;

    /**
     * Set app properties and soap headers
     *
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl, $options = array()) {
        $this->mod_edusharing_set_app_properties();
        parent::__construct($wsdl, $options);
        $this->mod_edusharing_set_soap_headers();
    }

    /**
     * Set soap headers
     *
     * @throws Exception
     */
    private function mod_edusharing_set_soap_headers() {
        try {
            $timestamp = round(microtime(true) * 1000);
            $signdata = $this->mod_edusharing_get_app_properties()->appid . $timestamp;
            $privkey = $this->mod_edusharing_get_app_properties()->private_key;
            $pkeyid = openssl_get_privatekey($privkey);
            openssl_sign($signdata, $signature, $pkeyid);
            $signature = base64_encode($signature);
            openssl_free_key($pkeyid);
            $headers = array();
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'appId', $this->mod_edusharing_get_app_properties()->appid);
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'timestamp', $timestamp);
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signature', $signature);
            $headers[] = new SOAPHeader('http://webservices.edu_sharing.org', 'signed', $signdata);
            parent::__setSoapHeaders($headers);
        } catch (Exception $e) {
            throw new Exception('Could not set soap headers - ' . $e->getMessage());
        }
    }

    /**
     * Set app properties
     */
    public function mod_edusharing_set_app_properties() {
        $this->appproperties = json_decode(get_config('edusharing', 'appProperties'));
    }

    /**
     * Get app properties
     * @throws Exception
     */
    public function mod_edusharing_get_app_properties() {
        if (empty($this->appproperties)) {
            throw new Exception('No appProperties found');
        }
        return $this->appproperties;
    }

}
