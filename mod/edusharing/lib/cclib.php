<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/**
  * Möglichkeiten Zugriff auf Exception Message:
  * 1. $e->detail->{"de.cc.ws.authentication.AuthenticationException"}->message
  * 2. $prop = "de.cc.ws.authentication.AuthenticationException";
  *    $e->detail->$prop->message
  * 3. $e->detail->{$e->detail->exceptionName}->message (zu empfehlen!!!)
  */

#echo "<pre>";
#debug_print_backtrace();


require_once 'Alfresco/Service/WebService/WebServiceFactory.php';

//include_once ('../admin/esrender/func/classes.new/EsApplication.php');
//include_once ('../admin/esrender/func/classes.new/EsApplications.php');
//include_once ('../admin/esrender/func/classes.new/ESApp.php');

//echo "<pre>";
//var_dump($_SERVER);
//die();

class CCWebServiceFactory {

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
    public function __construct($remote_app_id) {

        //.oO get CC homeconf
        $es = new ESApp();
        $app = $es->getApp(EDUSHARING_BASENAME);

        $remoteRep = $es->getAppByID($remote_app_id);
        $propArray = $remoteRep->prop_array;
    
        $this->authentication_service_wsdl = $propArray['authenticationwebservice_wsdl'];
        if ( empty($this->authentication_service_wsdl) ) {
            throw new Exception('No "authenticationwebservice_wsdl" configured.');
        }
    }

    /**
     *
     * @param string $home_app_id
     */
    public function CCAuthenticationGetRemoteTicket($home_app_id) {

        $alfservice =  new AlfrescoWebService($this->authentication_service_wsdl, array());
        $paramsTrusted = array("applicationId" => $home_app_id, "ticket" => session_id(), "ssoData" => getSsoData());
        try {
            $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
            $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;
            $_SESSION["USER"] -> ticket = $ticket;
            return $ticket;
        } catch(Exception $e) {
            print('Authentication failed');
        }
        
    }

    /**
     *
     * @param string $home_app_id
     */
    public function CCAuthenticationGetTicket($home_app_id)
    {
        $alfservice =  new AlfrescoWebService($this->authentication_service_wsdl, array());

        if (isset($_SESSION["USER"]->ticket)) {
            // ticket available.. is it valid?
            $params = array(
                "username" => get_edu_auth_key(),
                "ticket" => $_SESSION["USER"]->ticket
            );

            try {
                session_write_close();

                $alfReturn = $alfservice->checkTicket($params);

                session_start();

                if ( $alfReturn === true ) {
                    return $_SESSION["USER"]->ticket;
                }
            }
            catch(Exception $e)
            {
             // error_log( print_r($e, true) );
             	print('Invalid ticket');
                session_start();
            }

        }

        // no or invalid ticket available
        // request new ticket
        $paramsTrusted = array("applicationId" => $home_app_id, "ticket" => session_id(), "ssoData" => getSsoData());
        try {
            session_write_close();
            $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
            session_start();
            $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;
            $_SESSION["USER"] -> ticket = $ticket;
            return $ticket;
        } catch(Exception $e) {
            print('Authentication failed');
            session_start();
        }

        return false;
    } // eof CCAuthenticationgetTicket


    // --- get some nice text out of alfrescos error exceptions ---
    public function beautifyException($exception) {

        //error_log(print_r($exception, true));

        ob_start();
        //print_object($exception);
        $_exception = strtolower(ob_get_clean());



    $errorMSG = get_string('exc_MESSAGE', EDUSHARING_MODULE_NAME)."<br><br>";

        switch(1) {
            case (strpos($_exception, "sendactivationlink_success") !== false):
                $errorMSG .=  get_string('exc_SENDACTIVATIONLINK_SUCCESS', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "applicationaccess_not_activated_by_user") !== false):
                $errorMSG .=  get_string('exc_APPLICATIONACCESS_NOT_ACTIVATED_BY_USER', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "could not connect to host") !== false):
                $errorMSG .=  get_string('exc_COULD_NOT_CONNECT_TO_HOST', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "integrity violation") !== false):
                $errorMSG .=  get_string('exc_INTEGRITY_VIOLATION', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "invalid_application") !== false):
                $errorMSG .=  get_string('exc_INVALID_APPLICATION', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "error fetching http headers") !== false):
                $errorMSG .=  get_string('exc_ERROR_FETCHING_HTTP_HEADERS', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "node does not exist") !== false):
                $errorMSG .=  get_string('exc_NODE_DOES_NOT_EXIST', EDUSHARING_MODULE_NAME);
                break;

            case (strpos($_exception, "access_denied") !== false):
                $errorMSG .=  get_string('exc_ACCESS_DENIED', EDUSHARING_MODULE_NAME);
                break;

            default:
                $errorMSG .=  get_string('exc_UNKNOWN_ERROR', EDUSHARING_MODULE_NAME);
                break;
        }

        return $errorMSG;

    } // eof beautifyException

}//eof class CCWebServiceFactory

