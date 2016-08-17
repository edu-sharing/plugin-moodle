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

require_login();

$resid = optional_param('resId', 0, PARAM_INT); // edusharing instance ID

if ($resid) {
    $edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id'  => $resid), '*', MUST_EXIST);
} else {
    trigger_error('You must specify an instance ID', E_USER_WARNING);
}

require_login($edusharing->course, true);

$appproperties = json_decode(get_config('edusharing', 'appProperties'));
$repproperties = json_decode(get_config('edusharing', 'repProperties'));

$redirecturl = edusharing_get_redirect_url($edusharing, $appproperties, $repproperties);

$ts = $timestamp = round(microtime(true) * 1000);
$redirecturl .= '&ts=' . $ts;
$redirecturl .= '&sig=' . urlencode(edusharing_get_signature($appproperties->appid . $ts));
$redirecturl .= '&signed=' . urlencode($appproperties->appid . $ts);

redirect($redirecturl);

