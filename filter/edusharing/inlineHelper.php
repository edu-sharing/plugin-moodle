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
 * @copyright 2014 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(dirname(dirname(__FILE__))).'/mod/edusharing/lib.php');

$resId = optional_param('resId', 0, PARAM_INT); // edusharing instance ID 

if ($resId){
	$edusharing  = $DB->get_record(EDUSHARING_TABLE, array('id' => $resId), '*', MUST_EXIST);
} else {
    error('You must specify an instance ID');
}

require_login($edusharing -> course, true);

// load home-conf for prop-array, as the render-url is configured there.
$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$homeConf = $es->getHomeConf();
$app_id = $homeConf->prop_array['appid'];
 
$repID = $homeConf->prop_array['homerepid'];
$repositoryConf = $es->getAppByID($repID);
if ( ! $repositoryConf ) {
	print_error('Required repository not configured.');
	header('HTTP/1.1 500 Internal Server Error.');
	die();
}

$redirect_url = edusharing_get_redirect_url(
$edusharing,
$homeConf->prop_array,
$repositoryConf->prop_array);

$ts = $timestamp = round(microtime(true) * 1000);
$redirect_url .= '&ts=' . $ts;
$redirect_url .= '&sig=' . urlencode(getSignature($app_id . $ts));  
$redirect_url .= '&signed=' . urlencode($app_id . $ts);

redirect($redirect_url);

