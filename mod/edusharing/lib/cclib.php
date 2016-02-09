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
require_once dirname(__FILE__).'/Edusharing/EdusharingWebservice.php';
require_once dirname(__FILE__).'/../sigSoapClient.php';


class mod_edusharing_web_service_factory {

    /**
     * The url to authentication-service's WSDL.
     *
     * @var string
     */
    private $authentication_service_wsdl = '';

    /**
     *
     * @param string $remote_app_id
     * @throws Exception
     */
    public function __construct() {
        $repProperties = json_decode(get_config('edusharing', 'repProperties'));    
        $this -> authentication_service_wsdl = $repProperties -> authenticationwebservice_wsdl;
        if ( empty($this -> authentication_service_wsdl) ) {
            error_log('No "authenticationwebservice_wsdl" configured.');
        }
    }


    /**
     *
     * @param string $home_app_id
     */

    public function mod_edusharing_authentication_get_ticket($home_app_id) {

        //ticket available
        if (isset($_SESSION["USER"]->ticket)) {

            //ticket is younger than 10s, we must not check
            if(isset($_SESSION["USER"] -> ticketValidationTs) && time() - $_SESSION["USER"] -> ticketValidationTs < 10)
                return $_SESSION["USER"] -> ticket;
                    
            try {
                $eduService = new mod_edusharing_sig_soap_client($this -> authentication_service_wsdl, array());
            } catch (Exception $e) {
                print($this -> authentication_service_wsdl  . ' not reachable. Cannot utilize edu-sharing network.');
            }

            try {
                //ticket is older than 10s
                $params = array(
                    "username" => mod_edusharing_get_auth_key(),
                    "ticket" => $_SESSION["USER"]->ticket
                );
                
                $alfReturn = $eduService->checkTicket($params);
                
                if ( $alfReturn->checkTicketReturn ) {
                  $_SESSION["USER"] -> ticketValidationTs = time();
		          return $_SESSION["USER"]->ticket;
                }
            }
            catch(Exception $e) {
             	print('Invalid ticket. Cannot utilize edu-sharing network.');
                error_log($e);
            }

        }

        // no or invalid ticket available
        // request new ticket
        $paramsTrusted = array("applicationId" => $home_app_id, "ticket" => session_id(), "ssoData" => mod_edusharing_get_auth_data());
        try {
            $client = new mod_edusharing_sig_soap_client($this -> authentication_service_wsdl, array());
            $return = $client->authenticateByTrustedApp($paramsTrusted);
            $ticket = $return -> authenticateByTrustedAppReturn -> ticket;
            $_SESSION["USER"] -> ticket = $ticket;
            $_SESSION["USER"] -> ticketValidationTs = time();
            return $ticket;
        } catch(Exception $e) {
            print('Cannot utilize edu-sharing network because authentication failed. Error message : ' . $e -> getMessage());
            error_log($e);
        }

        return false;
    } // eof mod_edusharing_authentication_get_ticket

}//eof class mod_edusharing_web_service_factory

