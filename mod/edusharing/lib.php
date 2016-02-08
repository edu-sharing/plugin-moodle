<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
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
 * Library of interface functions and constants for module edusharing
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the edusharing specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', true);
// ini_set('log_errors', true);

define('EDUSHARING_MODULE_NAME', 'edusharing');
define('EDUSHARING_TABLE', 'edusharing');
define('EDUSHARING_BASENAME', 'esmain');

define('DISPLAY_MODE_DISPLAY', 'window');
define('DISPLAY_MODE_DOWNLOAD', 'download');
define('DISPLAY_MODE_INLINE', 'inline');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) .'/lib');

require_once 'Alfresco/Service/WebService/WebServiceFactory.php';
require_once 'Alfresco/Service/Node.php';
require_once 'Alfresco/Service/Version.php';
require_once 'Alfresco/Service/VersionHistory.php';
require_once 'Alfresco/Service/Session.php';
require_once 'Alfresco/Service/SpacesStore.php';
require_once dirname(__FILE__).'/lib/ESApp.php';
require_once dirname(__FILE__).'/lib/EsApplication.php';
require_once dirname(__FILE__).'/lib/EsApplications.php';
require_once dirname(__FILE__).'/lib/RenderParameter.php';
require_once dirname(__FILE__).'/lib/cclib.php';
require_once dirname(__FILE__).'/locallib.php';
require_once dirname(__FILE__).'/conf/cs_conf.php';

/**
 * If you for some reason need to use global variables instead of constants, do not forget to make them
 * global as this file can be included inside a function scope. However, using the global variables
 * at the module level is not a recommended.
 */
//global $NEWMODULE_GLOBAL_VARIABLE;
//$NEWMODULE_QUESTION_OF = array('Life', 'Universe', 'Everything');

/**
 * Module feature detection.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function edusharing_supports($feature) {

	/*
	 * ATTENTION: take extra care when modifying switch()-statement as we're
	 * using switch()'s fall-through mechanism to group features by true/false.
	 */
	switch($feature)
	{
		case FEATURE_MOD_ARCHETYPE:
			return MOD_ARCHETYPE_RESOURCE;
			break;
		case FEATURE_MOD_INTRO:
			return true;
			break;
		case FEATURE_GRADE_HAS_GRADE:
		case FEATURE_GRADE_OUTCOMES:
		case FEATURE_COMPLETION_TRACKS_VIEWS:
		case FEATURE_COMPLETION_HAS_RULES:
		case FEATURE_IDNUMBER:
		case FEATURE_GROUPS:
		case FEATURE_GROUPINGS:
		case FEATURE_GROUPMEMBERSONLY:
		case FEATURE_MOD_ARCHETYPE:
		case FEATURE_MOD_INTRO:
		case FEATURE_MODEDIT_DEFAULT_COMPLETION:
		case FEATURE_COMMENT:
		case FEATURE_RATE:
		case FEATURE_BACKUP_MOODLE2:
			return false;
		default:
			return false;
	}

	return null;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $edusharing An object from the form in mod_form.php
 * @return int The id of the newly inserted edusharing record
 */
function edusharing_add_instance(stdClass $edusharing)
{
	global $COURSE;
	global $CFG;
	global $DB;
	global $SESSION;

	$edusharing->timecreated = time();
	$edusharing->timemodified = time();

	# You may have to add extra stuff in here #
	$edusharing = _edusharing_postprocess($edusharing);

	$object_id = _edusharing_get_object_id_from_url($edusharing->object_url);
	if ( ! $object_id ) {
		error_log('Error parsing object-id from object-url "'.$edusharing->object_url.'"');
		return false;
	}

	$repository_id = _edusharing_get_repository_id_from_url($edusharing->object_url);
	if ( ! $repository_id ) {
		error_log('Error parsing repository-id from object-url "'.$edusharing->object_url.'"');
		return false;
	}

	//.oO get CC homeconf
	$es = new ESApp();
	$app = $es->getApp(EDUSHARING_BASENAME);
	$homeConf = $es->getHomeConf();
	if ( ! $homeConf )
	{
		error_log('Missing home-config.');
		return false;
	}

	if ( empty($homeConf->prop_array['homerepid'] ) )
	{
		error_log('Missing "homerepid" in home-conf.');
		return false;
	}

	$app_id = $homeConf->prop_array['appid'];
	if ( ! $app_id )
	{
		error_log('Missing "appid" in home-conf.');
		return false;
	}

	$repositoryConf = $es->getAppByID($repository_id);
	if ( ! $repositoryConf )
	{
		error_log('Missing config for repository "'.$repository_id.'"');
		return false;
	}

	// authenticate
	try {
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$wsdl = $repositoryConf->prop_array['authenticationwebservice_wsdl'];
		if ( ! $wsdl )
		{
			throw new Exception('No url for authentication-webservice (entry: "authenticationwebservice_wsdl") configured.');
		}

		$alfservice = new SoapClient($wsdl, array());
        $paramsTrusted = array("applicationId" => $app_id, "ticket" => session_id(), "ssoData" => getSsoData());
        $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
        $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;


		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log(print_r($exception, true));

		// restart stopped session
		session_start();

		print_error(_edusharing_beautify_exception($exception));
		print_footer("edu-sharing");

		return false;
	}

	// retrieve object-properties
	try {
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$alfrescoUrl = $repositoryConf->prop_array['alfresco_webservice_url'];
		if ( ! $alfrescoUrl )
		{
			throw new Exception('No base-url for native alfresco-services (entry: "alfresco_webservice_url") configured.');
		}

		$repository = new AlfrescoRepository($alfrescoUrl);
		$session = $repository->createSession($ticket);

		$store = new SpacesStore($session);
		$currentNode = $session->getNode($store, $object_id);

		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log(print_r($exception, true));

		// restart stopped session
		session_start();

		return false;
	}

	if ( ! empty($edusharing->object_version) )
	{
		$edusharing->object_version = _edusharing_fetch_object_version($currentNode);
	}

	// put the data of the new cc-resource into an array and create a neat XML-file out of it
	$data4xml = array("ccrender");

	$data4xml[1]["ccuser"]["id"] = $_SESSION["USER"]->email;
	$data4xml[1]["ccuser"]["name"] = $_SESSION["USER"]->firstname." ".$_SESSION["USER"]->lastname;

	$data4xml[1]["ccserver"]["ip"] = $_SERVER['SERVER_ADDR'];
	$data4xml[1]["ccserver"]["hostname"] = $_SERVER['SERVER_NAME'];
	$data4xml[1]["ccserver"]["mnet_localhost_id"] = $CFG->mnet_localhost_id;

	// move popup settings to array
	if (!empty($edusharing->popup)) {
		$parray = explode(',', $edusharing->popup);
		foreach ($parray as $key => $fieldstring) {
			$field = explode('=', $fieldstring);
			$popupfield->$field[0] = $field[1];
		}
	}

	// loop trough the list of keys... get the value... put into XML
	$keyList = array('resizable', 'scrollbars', 'directories', 'location', 'menubar', 'toolbar', 'status', 'width', 'height');
	foreach($keyList as $key) {
		$data4xml[1]["ccwindow"][$key] = isSet($popupfield->{$key}) ? $popupfield->{$key} : 0;
	}

	$data4xml[1]["ccwindow"]["forcepopup"] = isSet($edusharing->popup_window) ? 1 : 0;
	$data4xml[1]["ccdownload"]["download"] = isSet($edusharing->force_download) ? 1 : 0;

	$data4xml[1]["ccreferencen"]["reference"] = $object_id;

	$data4xml[1]["ccversion"]["version"] = $edusharing->window_versionshow;

	$myXML  = new RenderParameter();
	$xml = $myXML->getXML($data4xml);

	//
	$id = $DB->insert_record(EDUSHARING_TABLE, $edusharing);

	//.oOCC   introducing new added resource to the ALFRESCO-repository
	try
	{
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$connectionUrl = $repositoryConf->prop_array['usagewebservice_wsdl'];
		if ( ! $connectionUrl )
		{

			throw new Exception('Missing config-param "usagewebservice_wsdl".', E_ERROR);
		}

		$ccwsusage = new SoapCLient($connectionUrl);

		$params = array(
			"repositoryTicket" => $ticket,
			"repositoryUsername" => $_SESSION["USER"]->username,
			"lmsId" => $app_id,
			"courseId" => $edusharing->course,
			"parentNodeId" => $object_id,
			"resourceId" => $id,
			"appUser" => $_SESSION["USER"]->id,
			"appUserMail" => $_SESSION["USER"]->email,
			"fromUsed" => 1,
			"toUsed" => 99,
			"distinctPersons" => 0,
			"version" => $edusharing->window_version,
			"xmlParams" => $xml);

		// execute ALF-call
		$ccwsusage->setUsage($params);

		// restart stopped session
		session_start();
	}
	catch(SoapFault $exception)
	{
		error_log(print_r($exception, true));

		// restart stopped session
		session_start();

		$DB->delete_records(EDUSHARING_TABLE, array('id' => $id));

		print_error(_edusharing_beautify_exception($exception));

		return false;
	}

	return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $edusharing An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function edusharing_update_instance(stdClass $edusharing)
{
	global $CFG;
	global $COURSE;
	global $DB;

	// FIX: when editing a moodle-course-module the $edusharing->id will be named $edusharing->instance
	if ( ! empty($edusharing->instance) )
	{
		$edusharing->id = $edusharing->instance;
	}

	$edusharing->timemodified = time();

	// load previous state
	$memento = $DB->get_record(EDUSHARING_TABLE, array('id' => $edusharing->id));
	if ( ! $memento )
	{
		throw new Exception('Error loading edu-sharing memento.');
	}

	# You may have to add extra stuff in here #
	$edusharing = _edusharing_postprocess($edusharing);

	$object_id = _edusharing_get_object_id_from_url($edusharing->object_url);
	if ( ! $object_id ) {
		trigger_error('Error parsing object-id from object-url "'.$edusharing->object_url.'"', E_ERROR);
	}

	$repository_id = _edusharing_get_repository_id_from_url($edusharing->object_url);
	if ( ! $repository_id ) {
		trigger_error('Error parsing repository-id from object-url "'.$edusharing->object_url.'"', E_ERROR);
	}

	// fetch current node data
	$es = new ESApp();
	$app = $es->getApp(EDUSHARING_BASENAME);
	$homeConf = $es->getHomeConf();
	if ( ! $homeConf )
	{
		trigger_error('Missing home-config.');
	}

	$app_id = $homeConf->prop_array['appid'];
	if ( ! $app_id )
	{
		error_log('Missing "appid" in home-conf.');
	}

	$repositoryConf = $es->getAppByID($repository_id);
	if ( ! $repositoryConf )
	{
		trigger_error('Missing config for repository "'.$repository_id.'"');
	}

	// authenticate
	try {
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$wsdl = $repositoryConf->prop_array['authenticationwebservice_wsdl'];
		if ( ! $wsdl )
		{
			throw new Exception('No url for authentication-webservice (entry: "authenticationwebservice_wsdl") configured.');
		}

		$alfservice = new SoapClient($wsdl, array());
        $paramsTrusted = array("applicationId" => $app_id, "ticket" => session_id(), "ssoData" => getSsoData());
        $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
        $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;

		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log( print_r($exception, true) );

		// restart stopped session
		session_start();

		print_error(_edusharing_beautify_exception($exception));
		print_footer("edu-sharing");

		return false;
	}

	try {
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$alfrescoUrl = $repositoryConf->prop_array['alfresco_webservice_url'];
		if ( ! $alfrescoUrl )
		{
			throw new Exception('No base-url for native alfresco-services (entry: "alfresco_webservice_url") configured.');
		}

		$repository = new AlfrescoRepository($alfrescoUrl);
		$session = $repository->createSession($ticket);

		$store = new SpacesStore($session);
		$currentNode = $session->getNode($store, $object_id);

		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log(print_r($exception, true));

		// restart stopped session
		session_start();

		return false;
	}

	if ( $object_id != _edusharing_get_object_id_from_url($memento->object_url) )
	{
		// different object selected

		// delete  usage
		/*try {
			// stop session to avoid deadlock during edu-sharing call-backs
			session_write_close();

			$connectionUrl = $repositoryConf->prop_array['usagewebservice_wsdl'];
			if ( ! $connectionUrl )
			{
				error_log('Missing config-param "usagewebservice_wsdl".');
				return false;
			}

			$ccwsusage = new SoapCLient($connectionUrl);

			$params = array(
				"repositoryTicket" => $ticket,
				"repositoryUsername" => $_SESSION["USER"]->username,
				"lmsId" => $app_id,
				"courseId" => $memento->course,
				"appCurrentUserId" =>$_SESSION["USER"]->id,
				"parentNodeId" => $object_id,
				"resourceId" => $memento->id,
				"appUser" => $_SESSION["USER"]->id,
				"appSessionId" =>session_id()
			);

			$ccwsusage->deleteUsage($params);

			// restart stopped session
			session_start();
		}
		catch(Exception $exception)
		{
			// not fatal, just log the exception
			error_log( print_r($exception, true) );

			// restart stopped session
			session_start();
		}*/

		if ( ! empty($edusharing->object_version) )
		{
			$edusharing->object_version = _edusharing_fetch_object_version($currentNode);
		}
	}
	else
	{
		// same object selected
		if ( ! empty($edusharing->object_version) )
		{
			if ( ! empty($memento->object_version) )
			{
				// keep "old" object-version
				$edusharing->object_version = $memento->object_version;
			}
			else
			{
				// no "old" version-info available -> use current
				$edusharing->object_version = _edusharing_fetch_object_version($currentNode);
			}
		}
	}

	// put the data of the new cc-resource into an array and create a neat XML-file out of it
	$data4xml = array("ccrender");

	$data4xml[1]["ccuser"]["id"] = $_SESSION["USER"]->email;
	$data4xml[1]["ccuser"]["name"] = $_SESSION["USER"]->firstname." ".$_SESSION["USER"]->lastname;

	$data4xml[1]["ccserver"]["ip"] = $_SERVER['SERVER_ADDR'];
	$data4xml[1]["ccserver"]["hostname"] = $_SERVER['SERVER_NAME'];
	$data4xml[1]["ccserver"]["mnet_localhost_id"] = $CFG->mnet_localhost_id;

	// move popup settings to array
	if (!empty($edusharing->popup)) {
		$parray = explode(',', $edusharing->popup);
		foreach ($parray as $key => $fieldstring) {
			$field = explode('=', $fieldstring);
			$popupfield->$field[0] = $field[1];
		}
	}
	// loop trough the list of keys... get the value... put into XML
	$keyList = array('resizable', 'scrollbars', 'directories', 'location', 'menubar', 'toolbar', 'status', 'width', 'height');
	foreach($keyList as $key)
	{
		$data4xml[1]["ccwindow"][$key] = isSet($popupfield->{$key}) ? $popupfield->{$key} : 0;
	}

	$data4xml[1]["ccwindow"]["forcepopup"] = isSet($edusharing->popup_window) ? 1 : 0;
	$data4xml[1]["ccdownload"]["download"] = isSet($edusharing->force_download) ? 1 : 0;
	$data4xml[1]["cctracking"]["tracking"] = ($edusharing->tracking == 0) ? 0 : 1;
	$data4xml[1]["ccreferencen"]["reference"] = $object_id;
	$data4xml[1]["ccversion"]["version"] = $edusharing->window_versionshow;

	$myXML= new RenderParameter();
	$xml = $myXML->getXML($data4xml);

	try
	{
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		// throws exception on error, so no further checking required
		$DB->update_record(EDUSHARING_TABLE, $edusharing);

		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$connectionUrl = $repositoryConf->prop_array['usagewebservice_wsdl'];
		if ( ! $connectionUrl )
		{
			trigger_error('Missing config-param "usagewebservice_wsdl".', E_ERROR);
		}

		$ccwsusage = new SoapCLient($connectionUrl);

		$params = array(
			"repositoryTicket" => $ticket,
			"repositoryUsername" => $_SESSION["USER"]->username,
			"lmsId" => $app_id,
			"courseId" => $edusharing->course,
			"parentNodeId" => $object_id,
			"resourceId" => $edusharing->id,
			"appUser" => $_SESSION["USER"]->id,
			"appUserMail" => $_SESSION["USER"]->email,
			"fromUsed" => 1,
			"toUsed" => 99,
			"distinctPersons" => 0,
			"version" => $edusharing->window_version,
			"xmlParams" => $xml);

		$ccwsusage->setUsage($params);

		// restart stopped session
		session_start();
	}
	catch(SoapFault $exception)
	{
		error_log( print_r($exception, true) );

		// restart stopped session
		session_start();

		// roll back
		$DB->update_record(EDUSHARING_TABLE, $memento);

		print_error(_edusharing_beautify_exception($exception));

		return false;
	}

	return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function edusharing_delete_instance($id)
{
	global $DB;
	global $CFG;
	global $COURSE;

	// load from DATABASE to get object-data for repository-operations.
	if (! $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => $id))) {
		throw new Exception('Error loading edusharing-object from database.');
	}

	$object_id = _edusharing_get_object_id_from_url($edusharing->object_url);
	if ( ! $object_id ) {
		throw new Exception('Error parsing object-id from object-url "'.$edusharing->object_url.'"');
	}

	$repository_id = _edusharing_get_repository_id_from_url($edusharing->object_url);
	if ( ! $repository_id ) {
		throw new Exception('Error parsing repository-id from object-url "'.$edusharing->object_url.'"');
	}

	// load es-config
	$es = new ESApp();
	$app = $es->getApp(EDUSHARING_BASENAME);
	$homeConf = $es->getHomeConf();
	if ( ! $homeConf ) {
		throw new Exception('Missing home-config.');
	}

	$app_id = $homeConf->prop_array['appid'];
	if ( ! $app_id ) {
		throw new Exception('Missing "appid" in home-conf.');
	}

	$repositoryConf = $es->getAppByID($repository_id);
	if ( ! $repositoryConf ) {
		throw new Exception('Missing config for repository "'.$repository_id.'"');
	}

	// authenticate
	try {
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$wsdl = $repositoryConf->prop_array['authenticationwebservice_wsdl'];
		if ( ! $wsdl )
		{
			throw new Exception('No url for authentication-webservice (entry: "authenticationwebservice_wsdl") configured.');
		}

        $alfservice = new SoapClient($wsdl, array());
        $paramsTrusted = array("applicationId" => $app_id, "ticket" => session_id(), "ssoData" => getSsoData());
        $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
        $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;

		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log( print_r($exception, true) );

		// restart stopped session
		session_start();

		$Message = _edusharing_beautify_exception($exception);
		throw new Exception($Message);
	}

	try
	{
		// stop session to avoid deadlock during edu-sharing call-backs
		session_write_close();

		$connectionUrl = $repositoryConf->prop_array['usagewebservice_wsdl'];
		if ( ! $connectionUrl )
		{
			throw new Exception('No "usagewebservice_wsdl" configured.');
		}

		$ccwsusage = new SoapCLient($connectionUrl);

		// execute ALF-call
		$params = array(
			"repositoryTicket" => $ticket,
			"repositoryUsername" => $_SESSION["USER"]->username,
			"lmsId" => $app_id,
			"courseId" => $edusharing->course,
			"appCurrentUserId" =>$_SESSION["USER"]->id,
			"parentNodeId" => $object_id,
			"resourceId" => $edusharing->id,
			"appUser" => $_SESSION["USER"]->id,
			"appSessionId" => session_id()
		);

		$ccwsusage->deleteUsage($params);

		// restart stopped session
		session_start();
	}
	catch(Exception $exception)
	{
		error_log( print_r($exception, true) );

		// restart stopped session
		session_start();

		if ( 'Node does not exist' != substr($exception->getMessage(), 0, 19) )
		{
			$Message = _edusharing_beautify_exception($exception);
			throw new Exception($Message);
		}
	}

	// Usage is removed -> can delete from DATABASE now
	$DB->delete_records(EDUSHARING_TABLE, array('id' => $edusharing->id));

	return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo Finish documenting this function
 */
function edusharing_user_outline($course, $user, $mod, $edusharing)
{

	$return = new stdClass;

	$return->time = time();
	$return->info = 'edusharing_user_outline() - edu-sharing activity outline.';

	return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function edusharing_user_complete($course, $user, $mod, $edusharing)
{
	return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in edusharing activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo Finish documenting this function
 */
function edusharing_print_recent_activity($course, $isteacher, $timestart)
{
	return false;//True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function edusharing_cron()
{
	return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of edusharing. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $edusharingid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function edusharing_get_participants($edusharingid)
{
	return false;
}

/**
 * This function returns if a scale is being used by one edusharing
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $edusharingid ID of an instance of this module
 * @return mixed
 * @todo Finish documenting this function
 */
function edusharing_scale_used($edusharingid, $scaleid)
{
	global $DB;

	$return = false;

	//$rec = $DB->get_record(EDUSHARING_TABLE, array("id" => "$edusharingid", "scale" => "-$scaleid"));
	//
	//if (!empty($rec) && !empty($scaleid)) {
	//	$return = true;
	//}

	return $return;
}

/**
 * Checks if scale is being used by any instance of edusharing.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param $scaleid int
 * @return boolean True if the scale is used by any edusharing
 */
function edusharing_scale_used_anywhere($scaleid)
{
	global $DB;

// 	if ($scaleid and $DB->record_exists(EDUSHARING_TABLE, 'grade', -$scaleid)) {
// 		return true;
// 	} else {
		return false;
// 	}
}

/**
 * Execute post-install actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function edusharing_install()
{
	error_log('Installing mod_edusharing');

	$moduleDir = dirname(dirname(__FILE__));

	// copy required config-files to expected locations
	$configFiles = array(
		// general config
		'conf/cs_conf.php',
		// node configurations
		'conf/esmain/app-lms.properties.xml',
		'conf/esmain/app-renderer.properties.xml',
		'conf/esmain/app-repository.properties.xml',
		'conf/esmain/ccapp-registry.properties.xml',
		'conf/esmain/homeApplication.properties.xml',
		// web-service configs
		'services/authentication.wsdl',
		'services/permission.wsdl',
	);

	foreach( $configFiles as $file )
	{

		$destFilename = $moduleDir . DIRECTORY_SEPARATOR . $file;
		$srcFilename = $destFilename . '.dist';

		if ( ! file_exists($destFilename) )
		{
			if ( ! rename($srcFilename, $destFilename) )
			{
				error_log('Could not rename "'.$srcFilename.'" to "'.$destFilename.'".');
			}
			else
			{
				error_log('Successfully renamed "'.$srcFilename.'" to "'.$destFilename.'".');
			}
		}
	}

	return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function edusharing_uninstall()
{
	error_log('Uninstalling mod_edusharing');

	return true;
}

/**
 * Moodle will cache the outpu of this method, so it gets only called after
 * adding or updating an edu-sharing-resource, NOT every time the course
 * is shown.
 *
 * @param stdClass $coursemodule
 *
 * @return stdClass
 */
function edusharing_get_coursemodule_info($coursemodule)
{
	global $CFG;
	global $DB;

	//$info = new stdClass(); not for moodle 2.x
	$info = new cached_cm_info();

	$resource = $DB->get_record(EDUSHARING_TABLE, array('id' => $coursemodule->instance));
	if ( ! $resource )
	{
		trigger_error('Resource not found.', E_ERROR);
	}

    //resource is shown in new window so following options are obsolete (and btw did not work for moodle 2.x)

	/*$url = $CFG->wwwroot . '/mod/edusharing/view.php?id=' . urlencode($coursemodule->id);
    
	if ( ! empty($resource->popup_window) )
	{
		$options =
			'width='.htmlentities($resource->window_width)
			.',height='.htmlentities($resource->window_height)
			.',toolbar=no'
			.',location=' . (empty($resource->show_location_bar) ? 'no' : 'yes')
			.',menubar=' . (empty($resource->show_menu_bar) ? 'no' : 'yes')
			.',copyhistory=no'
			.',status=' . (empty($resource->show_status_bar) ? 'no' : 'yes')
			.',directories=no'
			.',scrollbars=' . (empty($resource->window_allow_scroll) ? 'no' : 'yes')
			.',resizable=' . (empty($resource->window_allow_resize) ? 'no' : 'yes');

		$info->extra = 'onclick="window.open(\''.$url.'\', \'\', \''.$options.'\'); return false;"';
	}*/
	
	if(!empty($resource -> popup_window)) {
	    $info->onclick = 'this.target=\'_blank\';';
	}

	return $info;
}

/**
 * Normalize form-values ...
 *
 * @param stdclass $edusharing
 *
 * @return stdClass
 *
 */
function _edusharing_postprocess($edusharing)
{
	global $CFG;
	global $COURSE;
	global $SESSION;

	if ( empty($edusharing->timecreated) )
	{
		$edusharing->timecreated = time();
	}

	$edusharing->timeupdated = time();

	/*
	 global $RESOURCE_WINDOW_OPTIONS;
	 $optionlist = array();
	 foreach ($RESOURCE_WINDOW_OPTIONS as $option)
	 {
		$optionlist[] = $option."=".$edusharing->$option;
		unset($edusharing->$option);
		}
		*/

	if (!empty($edusharing->force_download))
	{
		$edusharing->force_download = 1;
		$edusharing->popup_window= 0;
	}
	else if (!empty($edusharing->popup_window))
	{
		$edusharing->force_download = 0;
		$edusharing->options = '';
	}
	else
	{
		if (empty($edusharing->blockdisplay))
		{
			$edusharing->options = '';
		}

		$edusharing->popup_window = '';
	}

	$edusharing->window_versionshow = ($edusharing->window_versionshow == 'current') ? 0 : 1;
	$edusharing->tracking = empty($edusharing->tracking) ? 0 : $edusharing->tracking;

	if ( ! $edusharing->course )
	{
		$edusharing->course = $COURSE->id;
	}

	return $edusharing;
}

/**
 * Get the object-id from object-url.
 * E.g. "abc-123-xyz-456789" for "ccrep://homeRepository/abc-123-xyz-456789"
 *
 * @param string $object_url
 * @throws Exception
 * @return string
 */
function _edusharing_get_object_id_from_url($object_url)
{
	error_log($object_url);

	$object_id = parse_url($object_url, PHP_URL_PATH);
	if ( ! $object_id )
	{
		throw new Exception('Error reading object-id from object-url.');
	}

	$object_id = str_replace('/', '', $object_id);

	return $object_id;
}

/**
 * Get the repository-id from object-url.
 * E.g. "homeRepository" for "ccrep://homeRepository/abc-123-xyz-456789"
 *
 * @param string $object_url
 * @throws Exception
 * @return string
 */
function _edusharing_get_repository_id_from_url($object_url)
{
	$rep_id = parse_url($object_url, PHP_URL_HOST);
	if ( ! $rep_id )
	{
		throw new Exception('Error reading repository-id from object-url.');
	}

	return $rep_id;
}

/**
 *
 * @param Node $contentNode
 *
 * @return string
 */
function _edusharing_fetch_object_version(Node &$contentNode)
{
	$object_version = '0';

	// versioned objects shall have this
	if ( ! empty($contentNode->properties['{http://www.campuscontent.de/model/lom/1.0}version']) )
	{
		$object_version = $contentNode->properties['{http://www.campuscontent.de/model/lom/1.0}version'];
	}
	// fallback for non-versioned objects
	else if ( ! empty($contentNode->properties['{http://www.alfresco.org/model/content/1.0}versionLabel']) )
	{
		$object_version = $contentNode->properties['{http://www.alfresco.org/model/content/1.0}versionLabel'];
	}
	// don't break non-versioning repositories
	else
	{
		error_log('Error extracting object-version. Using fallback to "most-recent-version".');
		$object_version = '0';
	}

	return $object_version;
}

function _edusharing_beautify_exception($exception) {

	error_log(print_r($exception, true));

	ob_start();
	print_object($exception);
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

		case (strpos($_exception, "is not allowed") !== false):
			$errorMSG .=  get_string('exc_NO_PERMISSION', EDUSHARING_MODULE_NAME);
			break;

		default:
			$errorMSG .=  get_string('exc_UNKNOWN_ERROR', EDUSHARING_MODULE_NAME);
			break;
	}

	return $errorMSG;

} // eof beautifyException
