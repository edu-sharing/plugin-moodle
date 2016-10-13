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
 * Provide some methods for setting ang getting app properties
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * mod edusharing property helper
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusharing_app_property_helper {

    /**
     * Add ssl private and public key to app configuration
     */
    public function edusharing_add_ssl_keypair_to_home_config() {
        $sslkeypair = $this->edusharing_get_ssl_keypair();
        set_config('application_public_key', $sslkeypair['publicKey'], 'edusharing');
        set_config('application_private_key', $sslkeypair['privateKey'], 'edusharing');
    }

    /**
     * Get ssl private and public key from app configuration
     * @return array $sslkeypair
     */
    public function edusharing_get_ssl_keypair() {
        $sslkeypair = array();
        $res = openssl_pkey_new();
        openssl_pkey_export($res, $privatekey);
        $publickey = openssl_pkey_get_details($res);
        $publickey = $publickey["key"];
        $sslkeypair['privateKey'] = $privatekey;
        $sslkeypair['publicKey'] = $publickey;
        return $sslkeypair;
    }
}
