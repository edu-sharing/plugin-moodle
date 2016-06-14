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
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/Edusharing/EdusharingWebservice.php');
require_once(dirname(__FILE__).'/../sigSoapClient.php');


class mod_edusharing_web_service_factory {

    /**
     * The url to authentication-service's WSDL.
     *
     * @var string
     */
    private $authenticationservicewsdl = '';

    /**
     *
     * @param string $remote_app_id
     * @throws Exception
     */
    public function __construct() {
        $repproperties = json_decode(get_config('edusharing', 'repProperties'));
        $this->authenticationservicewsdl = $repproperties->authenticationwebservice_wsdl;
        if ( empty($this->authenticationservicewsdl) ) {
            trigger_error('No "authenticationwebservice_wsdl" configured.', E_USER_WARNING);
        }
    }


    /**
     *
     * @param string $homeappid
     */

    public function mod_edusharing_authentication_get_ticket($homeappid) {

        // ticket available
        if (isset($_SESSION["USER"]->ticket)) {

            // ticket is younger than 10s, we must not check
            if (isset($_SESSION["USER"]->ticketvalidationts)
                    && time() - $_SESSION["USER"]->ticketvalidationts < 10) {
                return $_SESSION["USER"]->ticket;
            }
            try {
                $eduservice = new mod_edusharing_sig_soap_client($this->authenticationservicewsdl, array());
            } catch (Exception $e) {
                trigger_error($this->authenticationservicewsdl  . ' not reachable. Cannot utilize edu-sharing network.', E_USER_WARNING);
            }

            try {
                // ticket is older than 10s
                $params = array(
                    "username"  => mod_edusharing_get_auth_key(),
                    "ticket"  => $_SESSION["USER"]->ticket
                );

                $alfreturn = $eduservice->checkTicket($params);

                if ($alfreturn->checkTicketReturn) {
                    $_SESSION["USER"]->ticketvalidationts = time();
                    return $_SESSION["USER"]->ticket;
                }
            } catch (Exception $e) {
                 trigger_error('Invalid ticket. Cannot utilize edu-sharing network.', E_USER_WARNING);
            }

        }

        // no or invalid ticket available
        // request new ticket
        $paramstrusted = array("applicationId"  => $homeappid, "ticket"  => session_id(), "ssoData"  => mod_edusharing_get_auth_data());
        try {
            $client = new mod_edusharing_sig_soap_client($this->authenticationservicewsdl, array());
            $return = $client->authenticateByTrustedApp($paramstrusted);
            $ticket = $return->authenticateByTrustedAppReturn->ticket;
            $_SESSION["USER"]->ticket = $ticket;
            $_SESSION["USER"]->ticketvalidationts = time();
            return $ticket;
        } catch (Exception $e) {
            trigger_error('Cannot utilize edu-sharing network because authentication failed. Error message: ' . $e->getMessage(), E_USER_WARNING);
        }

        return false;
    } // eof mod_edusharing_authentication_get_ticket

}//eof class mod_edusharing_web_service_factory

