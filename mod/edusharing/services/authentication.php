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


$wsdl = dirname(__FILE__) . '/authentication.wsdl';
if ( ! file_exists($wsdl) )
{
	$wsdl = dirname(__FILE__).'/authenticationservice.wsdl';
}

if ( ! file_exists($wsdl) )
{
	header('HTTP/1.1 500 Internal Server Error');
	trigger_error('WSDL "'.$wsdl.'" not found.', E_ERROR);
}

// deliver wsdl if requested
if ( isset($_GET['wsdl']) )
{
	echo file_get_contents($wsdl, false);
	exit();
}

require_once( dirname(__FILE__) . '/../lib/Service/ModMoodleService.php' );
require_once( dirname(__FILE__) . '/../lib/ESApp.php' );
require_once( dirname(__FILE__) . '/../lib/EsApplication.php' );
require_once( dirname(__FILE__) . '/../lib/EsApplications.php' );
require_once( dirname(__FILE__) . '/../conf/cs_conf.php' );


// include moodle-config
require_once( dirname(__FILE__) . '/../../../config.php' );

// a new moodle-session will be started here, so it has to closed ASAP again
require_once( dirname(__FILE__) . '/../../../lib/sessionlib.php' );
session_write_close();
ini_set('error_reporting', E_ALL | E_NOTICE | E_STRICT );
ini_set('display_errors', 0);
require_once( dirname(__FILE__) . '/../locallib.php' );


/**
 * Class provides the required authentication-service for moodle to take part in a
 * edu-sharing network.
 */
class CCAuthenticationService
extends ModMoodleService
{

	/**
	 * Test if ticket is bound to username.
	 *
	 * $wrappedParams must contain string $ticket and string $username as
	 * defined in "authenticationservice.wsdl".
	 *
	 * @param stdClass $wrappedParams
	 * @return array
	 */
	public function checkTicket($wrappedParams)
	{
		error_log('AuthenticationService::checkTicket(): invoked');

		// validate params
		if ( empty($wrappedParams->ticket) )
		{
			error_log('AuthenticationService::checkTicket(): Missing param "ticket".');
			throw new SoapFault('Sender', 'Missing param "ticket".');
		}

		if ( ! $this->_startMoodleSession($wrappedParams->ticket) )
		{
			error_log('AuthenticationService::checkTicket(): Error starting moodle-session.');
			return array("checkTicketReturn" => false);
		}

		// @TODO check for requested username
		if ( ! empty($_SESSION["USER"]->id) )
		{
			error_log('AuthenticationService::checkTicket(): Ticket valid.');
			return array("checkTicketReturn" => true);
		}

		error_log('AuthenticationService::checkTicket(): Ticket invalid.');

		return array("checkTicketReturn" => false);
	}

	/**
	 * Authenticate user from application "applicationId". Create user if
	 * requested by "createUser".
	 *
	 * string	applicationId
	 * string	username
	 * string	email
	 * string	ticket
	 * boolean	createUser
	 *
	 * @param stdClass $wrappedParams
	 * @return stdClass
	 */
	public function authenticateByApp($wrappedParams)
	{
		error_log('AuthenticationService::authenticateByApp(): invoked');

		if ( empty($wrappedParams->ticket) )
		{
			error_log('AuthenticationService::authenticateByApp(): No ticket provided.');
			return array("authenticateByAppReturn" => false);
		}

		if ( ! $this->_startMoodleSession($wrappedParams->ticket) )
		{
			error_log('AuthenticationService::authenticateByApp(): Error starting moodle-session.');
			return array("authenticateByAppReturn" => false);
		}

		if ( empty($_SESSION["USER"]->id) )
		{
			throw new SoapFault('AuthenticationService::authenticateByApp()', 'No user-id available.');
		}

		if ( empty($_SESSION["USER"]->email) )
		{
			throw new SoapFault('AuthenticationService::authenticateByApp()', 'No email-address available.');
		}

		if ( empty($_SESSION["USER"]->firstname) )
		{
			throw new SoapFault('AuthenticationService::authenticateByApp()', 'No firstname available.');
		}

		if ( empty($_SESSION["USER"]->lastname) )
		{
			throw new SoapFault('AuthenticationService::authenticateByApp()', 'No lastname available.');
		}

		if ( empty($_SESSION["USER"]->username) )
		{
			throw new SoapFault('AuthenticationService::authenticateByApp()', 'No username available.');
		}

		$d = new stdClass();
		$d->sessionid = $wrappedParams->ticket;
		$d->ticket = $wrappedParams->ticket;
		$d->userid = $_SESSION["USER"]->id;
		$d->email = $_SESSION['USER']->email;
		$d->givenname = $_SESSION["USER"]->firstname;
		$d->surname	= $_SESSION["USER"]->lastname;
		$d->username = get_edu_auth_key();

		return array('authenticateByAppReturn' => $d);
	}

}

libxml_disable_entity_loader(false);
$server = new SoapServer($wsdl);
$server->setClass("CCAuthenticationService");
$server->handle();
