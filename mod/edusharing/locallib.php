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
 * Internal library of functions for module edusharing
 *
 * All the edusharing specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');


function mod_edusharing_get_auth_key() {
    
    global $USER;
    
    if(array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        $EDU_AUTH_PARAM_NAME_USERID = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        return $_SESSION['sso'][$EDU_AUTH_PARAM_NAME_USERID];
    }
  
    if (empty($USER)) {
        $user_data = $_SESSION["USER"];     
    } else {
        $user_data = $USER;     
    }
    
    $EDU_AUTH_KEY = get_config('edusharing', 'EDU_AUTH_KEY');
    
    switch($EDU_AUTH_KEY) {
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
 */
function mod_edusharing_get_auth_data() {
    
    global $USER, $CFG;

    if (empty($USER)) {
        $user_data = $_SESSION["USER"];     
    } else {
        $user_data = $USER;     
    }
    
    if(array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        $authParams = array();
        foreach($_SESSION['sso'] as $key => $value) {
            $authParams[] = array('key' => $key, 'value' => $value);
        }
    } else {
        $EDU_AUTH_PARAM_NAME_USERID = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        $EDU_AUTH_PARAM_NAME_LASTNAME = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_LASTNAME');
        $EDU_AUTH_PARAM_NAME_FIRSTNAME = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_FIRSTNAME');
        $EDU_AUTH_PARAM_NAME_EMAIL = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_EMAIL');
        $EDU_AUTH_AFFILIATION = get_config('edusharing', 'EDU_AUTH_AFFILIATION');

        $authParams = array(
	        		array('key' => $EDU_AUTH_PARAM_NAME_USERID, 'value' => mod_edusharing_get_auth_key()),
	                array('key' => $EDU_AUTH_PARAM_NAME_LASTNAME, 'value' => $user_data -> lastname),
	                array('key' => $EDU_AUTH_PARAM_NAME_FIRSTNAME, 'value' => $user_data -> firstname),
	                array('key' => $EDU_AUTH_PARAM_NAME_EMAIL, 'value' => $user_data -> email),
	                array('key' => 'affiliation', 'value' => $EDU_AUTH_AFFILIATION),
               	);
    }
    
    if(get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS') == 'yes') {
    	$authParams[] = array('key' => 'globalgroups', 'value' => mod_edusharing_get_user_cohorts());
    }    
    return $authParams;
}

/*
 * get cohorts the user belongs to
 * */
function mod_edusharing_get_user_cohorts() {
	global $DB, $USER;
	$cohortMemberships = $DB -> get_records('cohort_members',array('userid' => $USER -> id));
	if($cohortMemberships) {
		foreach($cohortMemberships as $cohortMembership) {
			$cohort = $DB -> get_record('cohort', array('id' => $cohortMembership -> cohortid));	
			$ret[] = array(
					'id' => $cohortMembership -> cohortid,
					'contextid' => $cohort -> contextid,
					'name' => $cohort -> name,
					'idnumber' => $cohort -> idnumber
			);
		}
		return $ret;
	}
}

/**
 * Generate redirection-url
 *
 * @param stdCLass $edusharing
 * @return string
 */
function mod_edusharing_get_redirect_url(
    stdClass $edusharing,
    stdClass $appProperties,
    stdClass $repProperties,
    $display_mode = DISPLAY_MODE_DISPLAY)
{
    global $USER;
    
    $url = $appProperties -> cc_gui_url . '/renderingproxy';

    $url .= '?app_id='.urlencode($appProperties -> appid);

    $sessionId = session_id();
    $url .= '&session='.urlencode($sessionId);


    $rep_id = mod_edusharing_get_repository_id_from_url($edusharing -> object_url);
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
    $url .= '&version=' . urlencode($edusharing->object_version);
    $url .= '&language=' . urlencode($USER->lang);

    $ES_KEY = $appProperties -> blowfishkey;
    $ES_IV = $appProperties -> blowfishiv;

    $res = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
    mcrypt_generic_init($res, $ES_KEY, $ES_IV);
    $u = base64_encode(mcrypt_generic($res, mod_edusharing_get_auth_key()));
    mcrypt_generic_deinit($res);
    $url .= '&u='. rawurlencode($u);
    
    return $url;
}

function mod_edusharing_get_signature($data) {

    $appProperties = json_decode(get_config('edusharing', 'appProperties'));
    $priv_key = $appProperties -> private_key;
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
function mod_edusharing_get_current_users_language_code() {
    global $USER;

    $_my_lang = 'en_EN';

    if(isset($USER->lang)) {
        switch(strtolower(substr($USER->lang, 0, 2))) {
            case 'en':
                $_my_lang = 'en_EN';
            break;
            default:
                $_my_lang = 'de_DE';
        }
    }
    return $_my_lang;
}

