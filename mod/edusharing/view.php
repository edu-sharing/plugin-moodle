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
 * Prints a particular instance of edusharing
 * 
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $CFG, $PAGE;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // edusharing instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('edusharing', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id' => $cm->instance), '*', MUST_EXIST);
    $vId = $id;
    $courseId = $course -> id;
} elseif ($n) {
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $edusharing->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('edusharing', $edusharing->id, $course->id, false, MUST_EXIST);
    $vId = $edusharing -> id;
    $courseId = $course -> id;
} else {
    trigger_error('You must specify a course_module ID or an instance ID', E_USER_WARNING);
}

$PAGE->set_url('/mod/edusharing/view.php?id='.$vId);


require_login($course, true, $cm);

$appProperties = json_decode(get_config('edusharing', 'appProperties'));
$repProperties = json_decode(get_config('edusharing', 'repProperties'));

// authenticate to assure requesting user exists in home-repository
try {

    $wsdl = $repProperties -> authenticationwebservice_wsdl;
    $alfservice = new mod_edusharing_sig_soap_client($wsdl, array());
    $paramsTrusted = array("applicationId" => $appProperties -> appid, "ticket" => session_id(), "ssoData" => mod_edusharing_get_auth_data(),'repoId' => $appProperties -> homerepid);
    $alfReturn = $alfservice->authenticateByTrustedApp($paramsTrusted);
    $ticket = $alfReturn -> authenticateByTrustedAppReturn -> ticket;

}
catch(Exception $exception)
{
    trigger_error($exception -> getMessage(), E_USER_WARNING);
    return false;
}

$redirect_url = mod_edusharing_get_redirect_url($edusharing, $appProperties, $repProperties);
    
$ts = $timestamp = round(microtime(true) * 1000);
$redirect_url .= '&ts=' . $ts;
$redirect_url .= '&sig=' . urlencode(mod_edusharing_get_signature($appProperties -> appid . $ts));
$redirect_url .= '&signed=' . urlencode($appProperties -> appid . $ts);

$backlink = '';
if(empty($edusharing -> popup_window))
    $backlink = urlencode($CFG -> wwwroot . '/course/view.php?id=' . $courseId);
//if resource was opened with $edusharing -> popup_window disregarded
if(!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'modedit.php') !== false)
    $backlink = urlencode($_SERVER['HTTP_REFERER']);
if(!empty($backlink))
    $redirect_url .= '&backLink=' . $backlink;    

redirect($redirect_url);

