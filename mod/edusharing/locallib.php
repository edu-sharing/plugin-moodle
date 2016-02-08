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
 * Internal library of functions for module edusharing
 *
 * All the edusharing specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');


function get_edu_auth_key() {
    
	global $USER;
    
    if(array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        return $_SESSION['sso'][EDU_AUTH_PARAM_NAME_USERID];
    }
  
    if (empty($USER)) {
        $user_data = $_SESSION["USER"];  	
    } else {
        $user_data = $USER;  	
    }

	switch(EDU_AUTH_KEY) {
	    case 'id':
	        return $user_data -> id;
	    break;
	    
	    case 'idnumber':
	        return $user_data -> idnumber;
	    break;
	    
	    case 'email':
	        return $user_data -> email;
	    break;
        
        case 'username':
        default:
            return $user_data -> username;
	}
}


/* returns data for authByTrustedApp
 * if user is authenticated via shibboleth return shibboleth attributes
 * else return user auth key configured in /mod/edusharing/conf/cs_conf.php
 */
function getSsoData() {
    
    global $USER;

    if (empty($USER)) {
        $user_data = $_SESSION["USER"];     
    } else {
        $user_data = $USER;     
    }
    
    if(array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        $sso = array();
        foreach($_SESSION['sso'] as $key => $value) {
            $sso[] = array('key' => $key, 'value' => $value);
        }
        return $sso;
    } else {
        return array(array('key' => EDU_AUTH_PARAM_NAME_USERID, 'value' => get_edu_auth_key()),
                     array('key' => EDU_AUTH_PARAM_NAME_LASTNAME, 'value' => $user_data -> lastname),
                     array('key' => EDU_AUTH_PARAM_NAME_FIRSTNAME, 'value' => $user_data -> firstname),
                     array('key' => EDU_AUTH_PARAM_NAME_EMAIL, 'value' => $user_data -> email),
               );
    }
}

/*
function edusharing_encrypt_username()
{
	$handler = mcrypt_module_open('blowfish', '', 'cbc', '');
    $secretKey = ES_KEY;
    $iv= ES_IV;
    mcrypt_generic_init($handler, $secretKey, $iv);
    $decrypted = mcrypt_generic($handler, base64_decode($req_data['username']));
    mcrypt_generic_deinit($handler);
    $username = trim($decrypted);
	mcrypt_module_close($handler);
}
*/

/**
 * Generate redirection-url
 *
 * @param stdCLass $edusharing
 * @return string
 */
function edusharing_get_redirect_url(
	stdCLass $edusharing,
	array $appPropArray,
	array $repPropArray,
	$display_mode = DISPLAY_MODE_DISPLAY)
{
	global $USER;
	if ( empty($repPropArray['contenturl']) )
	{
		trigger_error('No repository-content-url configured.', E_ERROR);
	}

	$url = $repPropArray['contenturl'];

	$app_id = $appPropArray['appid'];
	if ( empty($app_id) )
	{
		trigger_error('No application-app-id configured.', E_ERROR);
	}

	$url .= '?app_id='.urlencode($app_id);

	$sessionId = session_id();
	$url .= '&session='.urlencode($sessionId);

	$rep_id = $repPropArray['appid'];
	if ( empty($rep_id) )
	{
		trigger_error('No repository-app-id configured.', E_ERROR);
	}

	$url .= '&rep_id='.urlencode($rep_id);

	$resourceRefenerence = str_replace('/', '', parse_url($edusharing->object_url, PHP_URL_PATH));
	if ( empty($resourceRefenerence) )
	{
		trigger_error('Error replacing resource-url "'.$edusharing->object_url.'".', E_ERROR);
	}

	$url .= '&obj_id='.urlencode($resourceRefenerence);

	$url .= '&resource_id='.urlencode($edusharing->id);
	$url .= '&course_id='.urlencode($edusharing->course);

    $url .= '&display='.urlencode($display_mode);

    $url .= '&width=' . urlencode($edusharing->window_width);
    $url .= '&height=' . urlencode($edusharing->window_height);
    
    $url .= '&language=' . urlencode($USER->lang);

    $ES_KEY = 'thetestkey';
    $ES_IV = 'initvect';
    $url .= '&u='. urlencode(base64_encode(mcrypt_cbc(MCRYPT_BLOWFISH, $ES_KEY, get_edu_auth_key(), MCRYPT_ENCRYPT, $ES_IV)));
    
    return $url;
}

function getSignature($data) {

    $es = new ESApp();
    $app = $es->getApp(EDUSHARING_BASENAME);
    $homeConf = $es->getHomeConf();
    $priv_key = $homeConf -> prop_array['private_key'];
    $pkeyid = openssl_get_privatekey($priv_key);      
    openssl_sign($data, $signature, $pkeyid);
    $signature = base64_encode($signature);
    openssl_free_key($pkeyid);    
    return $signature;
}

/**
 * Get locale-code for session-users language. Search the current session
 * and configuration.
 *
 * @return string
 */
function _edusharing_get_current_users_language_code() {
	global $USER;

	$_my_lang = 'en_EN';

	if(isset($USER->lang)) {
		switch(strtolower(substr($USER->lang, 0, 2))) {
			case 'de':
				$_my_lang = 'de_DE';
				break;
			case 'en':
			default:
				$_my_lang = 'en_EN';
		}
	}
	return $_my_lang;
}

