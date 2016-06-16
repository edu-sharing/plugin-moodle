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
 * Fetches object preview from repository
 *
 * @package    editor_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// create preview link with signature
require_once(dirname(__FILE__) . '/../../../config.php');
require_once(dirname(__FILE__) . '/../../../mod/edusharing/locallib.php');

require_login();

global $DB;

$resourceid = $_GET['resourceId'];

if (!$edusharing = $DB->get_record(EDUSHARING_TABLE, array('id'  => $resourceid))) {
    trigger_error('Error loading edusharing-object from database.', E_USER_WARNING);
}

$curlhandle = $edusharing->course;

$appproperties = json_decode(get_config('edusharing', 'appProperties'));

$previewservice = $appproperties->cc_gui_url . '/' . 'preview';

$objecturlparts = str_replace('ccrep://', '', $edusharing->object_url);
$objecturlparts = explode('/', $objecturlparts);

$repoid = $objecturlparts[0];
$nodeid = $objecturlparts[1];

$time = round(microtime(true) * 1000);

$url = $previewservice;
$url .= '?appId=' . $appproperties->appid;
$url .= '&courseId=' . $curlhandle;
$url .= '&repoId=' . $repoid;
$url .= '&proxyRepId=' . $appproperties->homerepid;
$url .= '&nodeId=' . $nodeid;
$url .= '&resourceId=' . $resourceid;
$url .= '&version=' . $edusharing->object_version;

$sig = urlencode(mod_edusharing_get_signature($appproperties->appid . $time));

$url .= '&sig=' . $sig;
$url .= '&ts=' . $time;

$curlhandle = curl_init($url);
curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curlhandle, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curlhandle, CURLOPT_HEADER, 0);
curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curlhandle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$output = curl_exec($curlhandle);
$mimetype = curl_getinfo($curlhandle, CURLINFO_CONTENT_TYPE);
curl_close($curlhandle);
header('Content-type: ' . $mimetype);echo $output;
exit();
