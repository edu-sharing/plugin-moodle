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
class mod_edusharing_app_property_helper {
    
    public function __construct() {

    }

    public function mod_edusharing_add_ssl_keypair_to_home_config() {
        $sslKeypair = $this->mod_edusharing_get_ssl_keypair();
        $appProperties = json_decode(get_config('edusharing', 'appProperties'));
        $appProperties->public_key = $sslKeypair['publicKey'];
        $appProperties->private_key = $sslKeypair['privateKey'];
        set_config('appProperties', json_encode($homeAppProperties), 'edusharing');
    }
    
    public function mod_edusharing_get_ssl_keypair() {
        $sslKeypair = array();
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $privatekey);
        $publickey = openssl_pkey_get_details($res);
        $publickey = $publickey["key"];
        $sslKeypair['privateKey'] = $privatekey;
        $sslKeypair['publicKey'] = $publickey;
        return $sslKeypair;
    }
    
    public function mod_edusharing_add_signature_redirector() {
        $sslKeypair = $this->mod_edusharing_get_ssl_keypair();
        $appProperties = json_decode(get_config('edusharing', 'appProperties'));
        $appProperties->signatureRedirector = $this->mod_edusharing_get_signatureRedirector();
        set_config('appProperties', json_encode($homeAppProperties), 'edusharing');
    }

    public function mod_edusharing_get_signatureRedirector() {
        global $CFG;
        return $CFG->wwwroot . '/filter/edusharing/signatureRedirector.php';
    }
    
}
