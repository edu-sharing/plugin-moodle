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
  * M�glichkeiten Zugriff auf Exception Message:
  * 1. $e->detail->{"de.cc.ws.authentication.AuthenticationException"}->message
  * 2. $prop = "de.cc.ws.authentication.AuthenticationException";
  *    $e->detail->$prop->message
  * 3. $e->detail->{$e->detail->exceptionName}->message (zu empfehlen!!!)
  */
#



class ESApp {

	private $CCWebServiceConfPath;
	private $CCWebServiceConfUrl;
	private $basename;
	private $Conf;

  public function getApp($basename){

     global $CFG;
     $_cnf_path = CC_CONF_PATH.$basename.'/';
     $n = new EsApplications(CC_CONF_PATH.$basename.'/'.CC_CONF_APPFILE);
     $li = $n->getFileList();
     foreach ($li as $key => $val){
         $n1 = new EsApplication($_cnf_path.$val);
         $n1->readProperties();
         $this->Conf[$val]= $n1;
     	}

  	return $this->Conf;
  	}

	/**
	 *
	 * @param string $app_id
	 */
	public function getAppByID($app_id)
	{
		if (isset($this->Conf['app-'.$app_id.'.properties.xml']))
		{
   			return $this->Conf['app-'.$app_id.'.properties.xml'];
		}

		return false;
	}

	public function getHomeConf() {

		if (isset($this->Conf['homeApplication.properties.xml']))
		{
   		return $this->Conf['homeApplication.properties.xml'];
		}

		return false;
	}

	public function setApp2Cache() {
		return false;
	}

	public function getRemoteAppData($session,$app_id) {
        die('es function deprecated since 1.8');

		try {

    $hc         = $this->getHomeConf();
    $remote_app = $this->getAppByID($app_id);

      $client = new SoapClient($remote_app->prop_array['authenticationwebservice_wsdl']);

			$params = array("applicationId" => $hc->prop_array['appid'],
							"username" => '',
							"email" => '',
							"ticket" => $session,
							"createUser" => false);

			$return = $client->authenticateByApp($params);

			return $return;


		} catch (Exception $e) {
			return $e;
		}
	} // eof CCAuthenticationgetTicket




/*
**
*
**
*/
	public function CCAuthenticationGetTicket() {

        die('es function deprecated since 1.8');

		$cUrl  = $this->getConnectionUrl();
		$cPath = $this->getConnectionPath();

		try {
			$alfservice =  new AlfrescoWebService($cUrl.$cPath, array());

			if (isset($_SESSION["USER"]->ticket)) {
				// ticket available.. is it valid?
				$params = array(
					"username" => get_edu_auth_key(),
					"ticket" => $_SESSION["USER"]->ticket,
				);

				$alfReturn = $alfservice->checkTicket($params);
				if ( $alfReturn === true ) {
					return $_SESSION["USER"]->ticket;
				}
			}

			// no or invalid ticket available
			// request new ticket
			$params = array(
				"applicationId" => "MOODLES",
				"username" => get_edu_auth_key(),
				"email" => $_SESSION["USER"]->email,
				"ticket" => session_id(),
				"createUser" => true,
			);

			$alfReturn = $alfservice->authenticateByApp($params);

			// got ticket... put into session and return it
			$ticket = $alfReturn->authenticateByAppReturn->ticket;
			$_SESSION["USER"]->ticket = $ticket;
			return $ticket;

		} catch (Exception $e) {
			return $e;
		}
	} // eof CCAuthenticationgetTicket


	// --- get some nice text out of alfrescos error exceptions ---
	public function beautifyException($exception) {

		ob_start();
		va_dump($exception);
		$exceptionDump = ob_get_clean();
		error_log($exceptionDump);
		ob_end();

		// still crap ... alf exceptions are not consistent/unified/defined yet :(
		switch (1) {
			case (isSet($exception->faultstring)):
				$_exception = $exception->faultstring;
				break;
			case (isset($exception->detail->{$exception->detail->exceptionName})):
				$_exception =$exception->detail->{$exception->detail->exceptionName};
				break;
			default:
				$_exception = "unknown";
		}

			/**
				* M�glichkeiten Zugriff auf Exception Message:
				* 1. $e->detail->{"de.cc.ws.authentication.AuthenticationException"}->message
				* 2. $prop = "de.cc.ws.authentication.AuthenticationException";
				*    $e->detail->$prop->message
				* 3. $e->detail->{$e->detail->exceptionName}->message (zu empfehlen!!!)
				*/

		/* error codes/stack traces, got from CC_ALFRESCO ... need to be 'beautified'
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

		  ["faultstring"]=>
		  string(25) "Could not connect to host"

		  ["faultstring"]=>
		  string(122) "SOAP-ERROR: Parsing WSDL: Couldn't load from 'somewhere'"
		  ["faultcode"]=>
		  string(4) "WSDL"

		  ["faultcode"]=>
		  string(31) "soapenv:Server.generalException"
		  ["detail"]=>
		  object(stdClass)#210 (4) {
		    ["de.cc.ws.authentication.AuthenticationException"]=>
		    object(stdClass)#209 (2) {
		      ["cause"]=>
		      NULL
		      ["message"]=>
		      string(39) "APPLICATIONACCESS_NOT_ACTIVATED_BY_USER"
		    }
		    ["exceptionName"]=>
		    string(47) "de.cc.ws.authentication.AuthenticationException"

	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

			  ["faultcode"]=>
			  string(31) "soapenv:Server.generalException"
			  ["detail"]=>
			  object(stdClass)#145 (5) {
			    ["faultData"]=>
			    object(stdClass)#142 (2) {
			      ["errorCode"]=>
			      string(1) "0"
			      ["message"]=>
			      string(160) "org.alfresco.service.cmr.repository.InvalidNodeRefException: Node does not exist: workspace://SpacesStore/ccrep://undefined/8b5b2b84-6949-4d27-9bf2-9b997508729b"
			    }
			    ["RepositoryFault"]=>
			    object(stdClass)#146 (2) {
			      ["errorCode"]=>
			      string(1) "0"
			      ["message"]=>
			      string(160) "org.alfresco.service.cmr.repository.InvalidNodeRefException: Node does not exist: workspace://SpacesStore/ccrep://undefined/8b5b2b84-6949-4d27-9bf2-9b997508729b"
			    }
			    ["exceptionName"]=>
			    string(55) "org.alfresco.repo.webservice.repository.RepositoryFault"
			    ["stackTrace"]=>
			    string(2314) "
				at org.alfresco.repo.webservice.repository.RepositoryWebService.executeQuery(RepositoryWebService.java:176)
				at org.alfresco.repo.webservice.repository.RepositoryWebService.queryChildren(RepositoryWebService.java:207)
				at sun.reflect.GeneratedMethodAccessor574.invoke(Unknown Source)
				at sun.reflect.DelegatingMethodAccessorImpl.invoke(DelegatingMethodAccessorImpl.java:25)
				at java.lang.reflect.Method.invoke(Method.java:585)
				at org.apache.axis.providers.java.RPCProvider.invokeMethod(RPCProvider.java:397)

	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-

			  ["faultcode"]=>
			  string(31) "soapenv:Server.generalException"
			  ["detail"]=>
			  object(stdClass)#399 (5) {
			    ["faultData"]=>
			    object(stdClass)#398 (2) {
			      ["errorCode"]=>
			      string(1) "0"
			      ["message"]=>
			      string(148) "org.alfresco.repo.security.permissions.AccessDeniedException: Access Denied.  You do not have the appropriate permissions to perform this operation."
			    }
			    ["RepositoryFault"]=>
			    object(stdClass)#400 (2) {
			      ["errorCode"]=>
			      string(1) "0"
			      ["message"]=>
			      string(148) "org.alfresco.repo.security.permissions.AccessDeniedException: Access Denied.  You do not have the appropriate permissions to perform this operation."
			    }
			    ["exceptionName"]=>
			    string(55) "org.alfresco.repo.webservice.repository.RepositoryFault"
			    ["stackTrace"]=>
			    string(2314) "
				at org.alfresco.repo.webservice.repository.RepositoryWebService.executeQuery(RepositoryWebService.java:176)
				at org.alfresco.repo.webservice.repository.RepositoryWebService.queryChildren(RepositoryWebService.java:207)
				at sun.reflect.GeneratedMethodAccessor789.invoke(Unknown Source)

	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-
	+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-+-


	  */

		//echo("<pre>");var_dump($exception);die;

	switch(1) {
	    case (strpos($_exception, "SENDACTIVATIONLINK_SUCCESS") !== false):
	    	return get_string('exc_SENDACTIVATIONLINK_SUCCESS','edu-sharing');
	    case (strpos($_exception, "APPLICATIONACCESS_NOT_ACTIVATED_BY_USER") !== false):
	    	return get_string('exc_APPLICATIONACCESS_NOT_ACTIVATED_BY_USER','edu-sharing');
	    case (strpos($_exception, "Could not connect to host") !== false):
	    	return get_string('exc_COULD_NOT_CONNECT_TO_HOST','edu-sharing');
		default:
			return get_string('exc_UNKNOWN_ERROR','edu-sharing');
    }

	} // eof beautifyException

}//eof class
