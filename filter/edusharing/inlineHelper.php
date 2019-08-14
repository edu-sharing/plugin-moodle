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
 * @package    filter_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/edusharing/lib.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/edusharing/lib/cclib.php');

require_sesskey();

$resid = optional_param('resId', 0, PARAM_INT); // edusharing instance ID
$childobject_id = optional_param('childobject_id', '', PARAM_TEXT);

if ($resid) {
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id'  => $resid), '*', MUST_EXIST);
} else {
    trigger_error(get_string('error_missing_instance_id', 'filter_edusharing'), E_USER_WARNING);
}

require_login($edusharing->course, true);

$redirecturl = edusharing_get_redirect_url($edusharing);
$ts = $timestamp = round(microtime(true) * 1000);
$redirecturl .= '&ts=' . $ts;
$data = get_config('edusharing', 'application_appid') . $ts . edusharing_get_object_id_from_url($edusharing->object_url);
$redirecturl .= '&sig=' . urlencode(edusharing_get_signature($data));
$redirecturl .= '&signed=' . urlencode($data);
$redirecturl .= '&closeOnBack=true';
$cclib = new mod_edusharing_web_service_factory();
$redirecturl .= '&ticket=' . urlencode(base64_encode(edusharing_encrypt_with_repo_public($cclib -> edusharing_authentication_get_ticket())));

if($childobject_id)
    $redirecturl .= '&childobject_id=' . $childobject_id;

redirect($redirecturl);

