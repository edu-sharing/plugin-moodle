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
 * @package    editor
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// create preview link with signature
require_once dirname(__FILE__) . '/../../../config.php';
require_once dirname(__FILE__) . '/../../../mod/edusharing/locallib.php';

require_login();

global $DB;

$resourceId = $_GET['resourceId'];

if (!$edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => $resourceId))) {
    throw new Exception('Error loading edusharing-object from database.');
}

$courseId = $edusharing -> course;

$appProperties = json_decode(get_config('edusharing', 'appProperties'));

$previewService = $appProperties -> cc_gui_url . '/' . 'preview';

$objectUrlParts = str_replace('ccrep://', '', $edusharing -> object_url);
$objectUrlParts = explode('/', $objectUrlParts);

$repoId = $objectUrlParts[0];
$nodeId = $objectUrlParts[1];

$time = round(microtime(true) * 1000);

$url = $previewService;
$url .= '?appId=' . $appProperties -> appid;
$url .= '&courseId=' . $courseId;
$url .= '&repoId=' . $repoId;
$url .= '&proxyRepId=' . $appProperties -> homerepid;
$url .= '&nodeId=' . $nodeId;
$url .= '&resourceId=' . $resourceId;
$url .= '&version=' . $edusharing -> object_version;

$sig = urlencode(mod_edusharing_get_signature($appProperties -> appid . $time));

$url .= '&sig=' . $sig;
$url .= '&ts=' . $time;

$curl_handle = curl_init($url); 
curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$output = curl_exec($curl_handle); 
$mimetype = curl_getinfo($curl_handle, CURLINFO_CONTENT_TYPE);
curl_close($curl_handle);      
header('Content-type: ' . $mimetype);echo $output;
exit();
