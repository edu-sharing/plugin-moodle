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
 * Prints a particular instance of edusharing
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/lib/cclib.php');

global $CFG, $PAGE;

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // edusharing instance ID - it should be named as the first character of the module

if ($id) {
    $cm         = get_coursemodule_from_id('edusharing', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id'  => $cm->course), '*', MUST_EXIST);
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id'  => $cm->instance), '*', MUST_EXIST);
    $vid = $id;
    $courseid = $course->id;
} else if ($n) {
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id'  => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id'  => $edusharing->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('edusharing', $edusharing->id, $course->id, false, MUST_EXIST);
    $vid = $edusharing->id;
    $courseid = $course->id;
} else {
    trigger_error(get_string('error_detect_course', 'edusharing'), E_USER_WARNING);
}

$PAGE->set_url('/mod/edusharing/view.php?id='.$vid);


require_login($course, true, $cm);

// Authenticate to assure requesting user exists in home-repository.
try {
    $servicefactory = new mod_edusharing_web_service_factory();
    $ticket = $servicefactory->edusharing_authentication_get_ticket();
} catch (Exception $exception) {
    trigger_error($exception->getMessage(), E_USER_WARNING);
    return false;
}

$redirecturl = edusharing_get_redirect_url($edusharing);
$ts = $timestamp = round(microtime(true) * 1000);
$redirecturl .= '&ts=' . $ts;
$data = get_config('edusharing', 'application_appid') . $ts . edusharing_get_object_id_from_url($edusharing->object_url);
$redirecturl .= '&sig=' . urlencode(edusharing_get_signature($data));
$redirecturl .= '&signed=' . urlencode($data);

$backAction = '&closeOnBack=true';
if (empty($edusharing->popup_window)) {
    $backAction = '&backLink=' . urlencode($CFG->wwwroot . '/course/view.php?id=' . $courseid);
}
if (!empty($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'modedit.php') !== false) {
    $backAction = '&backLink=' . urlencode($_SERVER['HTTP_REFERER']);
}

$redirecturl .= $backAction;

$cclib = new mod_edusharing_web_service_factory();
$redirecturl .= '&ticket=' . urlencode(base64_encode(edusharing_encrypt_with_repo_public($cclib -> edusharing_authentication_get_ticket())));

redirect($redirecturl);

