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
 * Handle some webservice functions
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../sigSoapClient.php');

/**
 * Handle some webservice functions
 *
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusharing_web_service_factory {

    /**
     * The url to authentication-service's WSDL.
     *
     * @var string
     */
    private $authenticationservicewsdl = '';

    /**
     * Get repository properties and set auth service url
     *
     * @throws Exception
     */
    public function __construct() {
        $this->authenticationservicewsdl = get_config('edusharing', 'repository_authenticationwebservice_wsdl');
        if ( empty($this->authenticationservicewsdl) ) {
            trigger_error(get_string('error_missing_authwsdl', 'edusharing'), E_USER_WARNING);
        }
    }


    /**
     * Get repository ticket
     * Check existing ticket vor validity
     * Request a new one if existing ticket is invalid
     * @param string $homeappid
     */
    public function edusharing_authentication_get_ticket() {

        global $USER;

        // Ticket available.
        if (isset($USER->edusharing_userticket)) {

            // Ticket is younger than 10s, we must not check.
            if (isset($USER->edusharing_userticketvalidationts)
                    && time() - $USER->edusharing_userticketvalidationts < 10) {
                return $USER->edusharing_userticket;
            }
            try {
                $eduservice = new mod_edusharing_sig_soap_client($this->authenticationservicewsdl, array());
            } catch (Exception $e) {
                trigger_error($this->authenticationservicewsdl . ' ' .
                        get_string('error_authservice_not_reachable', 'edusharing') , E_USER_WARNING);
            }

            try {
                // Ticket is older than 10s.
                $params = array(
                    "username"  => edusharing_get_auth_key(),
                    "ticket"  => $USER->edusharing_userticket
                );

                $alfreturn = $eduservice->checkTicket($params);

                if ($alfreturn->checkTicketReturn) {
                    $USER->edusharing_userticketvalidationts = time();
                    return $USER->edusharing_userticket;
                }
            } catch (Exception $e) {
                 trigger_error(get_string('error_invalid_ticket', 'edusharing'), E_USER_WARNING);
            }

        }

        // No or invalid ticket available - request new ticket.
        $paramstrusted = array("applicationId"  => get_config('edusharing', 'application_appid'),
                        "ticket"  => session_id(), "ssoData"  => edusharing_get_auth_data());
        try {
            $client = new mod_edusharing_sig_soap_client($this->authenticationservicewsdl);
            $return = $client->authenticateByTrustedApp($paramstrusted);
            $ticket = $return->authenticateByTrustedAppReturn->ticket;
            $USER->edusharing_userticket = $ticket;
            $USER->edusharing_userticketvalidationts = time();
            return $ticket;
        } catch (Exception $e) {
            trigger_error(get_string('error_auth_failed', 'edusharing') . ' ' . $e, E_USER_WARNING);
        }
        return false;
    }
}
