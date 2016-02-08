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
 * Prints a particular instance of edusharing
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // edusharing instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('edusharing', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $edusharing->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('edusharing', $edusharing->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

add_to_log($course->id, 'edusharing', 'view', "view.php?id=$cm->id", $edusharing->name, $cm->id);

// load home-conf for prop-array, as the render-url is configured there.
$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$homeConf = $es->getHomeConf();
$app_id = $homeConf->prop_array['appid'];

$repID = _edusharing_get_repository_id_from_url($edusharing->object_url);
if ( ! $repID ) {
	print_error('Error parsing repository-id.');
	header('HTTP/1.1 500 Internal Server Error.');
	die();
}

/*
 * as every renderer is tied to a certain repository we have to retrieve the
 * repository's config to access the renderer's url
 */
$repositoryConf = $es->getAppByID($repID);
if ( ! $repositoryConf ) {
	print_error('Required repository not configured.');
	header('HTTP/1.1 500 Internal Server Error.');
	die();
}

// authenticate to assure requesting user exists in home-repository
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
	// restart stopped session
	session_start();

	error_log( print_r($exception, true) );

	print_error(_edusharing_beautify_exception($exception));
	print_footer("edu-sharing");

	return false;
}

$redirect_url = edusharing_get_redirect_url(
	$edusharing,
	$homeConf->prop_array,
	$repositoryConf->prop_array);
	
    $ts = $timestamp = round(microtime(true) * 1000);
    $redirect_url .= '&ts=' . $ts;
    $redirect_url .= '&sig=' . urlencode(getSignature($app_id . $ts));  

redirect($redirect_url);

