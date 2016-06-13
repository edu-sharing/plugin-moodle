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

/*
 * Copyright (C) 2005 Alfresco, Inc.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * As a special exception to the terms and conditions of version 2.0 of
 * the GPL, you may redistribute this Program in connection with Free/Libre
 * and Open Source Software ("FLOSS") applications as described in Alfresco's
 * FLOSS exception. You should have recieved a copy of the text describing
 * the FLOSS exception, and it is also available here:
 * http://www.alfresco.com/legal/licensing"
 */

/**
 *
 * @package mod
 * @subpackage edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusharing_web_service extends SoapClient {

    private $securityextns = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd";

    private $wsutilityns = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd";

    private $passwordtype = "http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-username-token-profile-1.0#PasswordText";

    private $ticket;

    public function __construct($wsdl, $options = array('trace'  => true, 'exceptions'  => true), $ticket = null) {
        // Store the current ticket
        $this->ticket = $ticket;

        // Call the base class
        @ parent::__construct($wsdl, $options); // if SOAP server is unreachable (unable to get WSDL
                                                // file) suppress the php warning... errorhandling
                                                // kicks in later.
    }

    public function __call($functionname, $arguments) {
        return $this->__soapCall($functionname, $arguments);
    }

    public function __soapCall($functionname, $arguments, $options = array(), $inputheaders = array(),
            &$outputheaders = array()) {
        if (isset($this->ticket)) {
            // Automatically add a security header
            $inputheaders[] = new SoapHeader($this->securityExtNS, "Security", null, 1);
        }

        return parent::__soapCall($functionname, $arguments, $options, $inputheaders,
                $outputheaders);
    }

    public function __doRequest($request, $location, $action, $version, $oneway = 0) {
        // If this request requires authentication we have to manually construct the
        // security headers.
        if (isset($this->ticket)) {
            $dom = new DOMDocument("1.0");
            $dom->loadXML($request);

            $securityheader = $dom->getElementsByTagName("Security");

            if ($securityheader->length != 1) {
                throw new Exception(
                        "Expected length: 1, Received: " . $securityheader->length .
                                 ". No Security Header, or more than one element called Security!");
            }

            $securityheader = $securityheader->item(0);

            // Construct Timestamp Header
            $timestamp = $dom->createElementNS($this->wsUtilityNS, "Timestamp");
            $createddate = date("Y-m-d\TH:i:s\Z",
                    mktime(date("H") + 24, date("i"), date("s"), date("m"), date("d"), date("Y")));
            $expiresdate = date("Y-m-d\TH:i:s\Z",
                    mktime(date("H") + 25, date("i"), date("s"), date("m"), date("d"), date("Y")));
            $created = new DOMElement("Created", $createddate, $this->wsUtilityNS);
            $expires = new DOMElement("Expires", $expiresdate, $this->wsUtilityNS);
            $timestamp->appendChild($created);
            $timestamp->appendChild($expires);

            // Construct UsernameToken Header
            $usernametoken = $dom->createElementNS($this->securityExtNS, "UsernameToken");
            $username = new DOMElement("Username", "username", $this->securityExtNS);
            $password = $dom->createElementNS($this->securityExtNS, "Password");
            $typeattr = new DOMAttr("Type", $this->passwordType);
            $password->appendChild($typeattr);
            $password->appendChild($dom->createTextNode($this->ticket));
            $usernametoken->appendChild($username);
            $usernametoken->appendChild($password);

            // Construct Security Header
            $securityheader->appendChild($timestamp);
            $securityheader->appendChild($usernametoken);

            // Save the XML Request
            $request = $dom->saveXML();
        }

        return parent::__doRequest($request, $location, $action, $version, $oneway);
    }
}
