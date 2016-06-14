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

    if (array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        return $_SESSION['sso'][$eduauthparamnameuserid];
    }

    if (empty($USER)) {
        $userdata = $_SESSION["USER"];
    } else {
        $userdata = $USER;
    }

    $eduauthkey = get_config('edusharing', 'EDU_AUTH_KEY');

    switch($eduauthkey) {
        case 'id':
            return $userdata->id;
        break;

        case 'idnumber':
            return $userdata->idnumber;
        break;

        case 'email':
            return $userdata->email;
        break;

        case 'username':
        default:
            return $userdata->username;
    }
}


/* returns data for authByTrustedApp
 */
function mod_edusharing_get_auth_data() {

    global $USER, $CFG;

    if (empty($USER)) {
        $userdata = $_SESSION["USER"];
    } else {
        $userdata = $USER;
    }

    if (array_key_exists('sso', $_SESSION) && !empty($_SESSION['sso'])) {
        $authparams = array();
        foreach ($_SESSION['sso'] as $key => $value) {
            $authparams[] = array('key'  => $key, 'value'  => $value);
        }
    } else {
        $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        $eduauthparamnamelastname = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_LASTNAME');
        $eduauthparamnamefirstname = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_FIRSTNAME');
        $eduauthparamnameemail = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_EMAIL');
        $eduauthaffiliation = get_config('edusharing', 'EDU_AUTH_AFFILIATION');

        $authparams = array(
                    array('key'  => $eduauthparamnameuserid, 'value'  => mod_edusharing_get_auth_key()),
                    array('key'  => $eduauthparamnamelastname, 'value'  => $userdata->lastname),
                    array('key'  => $eduauthparamnamefirstname, 'value'  => $userdata->firstname),
                    array('key'  => $eduauthparamnameemail, 'value'  => $userdata->email),
                    array('key'  => 'affiliation', 'value'  => $eduauthaffiliation),
                   );
    }

    if (get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS') == 'yes') {
        $authparams[] = array('key'  => 'globalgroups', 'value'  => mod_edusharing_get_user_cohorts());
    }
    return $authparams;
}

/*
 * get cohorts the user belongs to
 * */
function mod_edusharing_get_user_cohorts() {
    global $DB, $USER;
    $cohortmemberships = $DB->get_records('cohort_members', array('userid'  => $USER->id));
    if ($cohortmemberships) {
        foreach ($cohortmemberships as $cohortmembership) {
            $cohort = $DB->get_record('cohort', array('id'  => $cohortmembership->cohortid));
            $ret[] = array(
                    'id'  => $cohortmembership->cohortid,
                    'contextid'  => $cohort->contextid,
                    'name'  => $cohort->name,
                    'idnumber'  => $cohort->idnumber
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
    stdClass $appproperties,
    stdClass $repproperties,
    $displaymode = DISPLAY_MODE_DISPLAY) {
    global $USER;

    $url = $appproperties->cc_gui_url . '/renderingproxy';

    $url .= '?app_id='.urlencode($appproperties->appid);

    $sessionid = session_id();
    $url .= '&session='.urlencode($sessionid);

    $repid = mod_edusharing_get_repository_id_from_url($edusharing->object_url);
    $url .= '&rep_id='.urlencode($repid);

    $resourcerefenerence = str_replace('/', '', parse_url($edusharing->object_url, PHP_URL_PATH));
    if ( empty($resourcerefenerence) ) {
        trigger_error('Error replacing resource-url "'.$edusharing->object_url.'".', E_USER_WARNING);
    }

    $url .= '&obj_id='.urlencode($resourcerefenerence);

    $url .= '&resource_id='.urlencode($edusharing->id);
    $url .= '&course_id='.urlencode($edusharing->course);

    $url .= '&display='.urlencode($displaymode);

    $url .= '&width=' . urlencode($edusharing->window_width);
    $url .= '&height=' . urlencode($edusharing->window_height);
    $url .= '&version=' . urlencode($edusharing->object_version);
    $url .= '&language=' . urlencode($USER->lang);

    $eskey = $appproperties->blowfishkey;
    $esiv = $appproperties->blowfishiv;

    $res = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
    mcrypt_generic_init($res, $eskey, $esiv);
    $u = base64_encode(mcrypt_generic($res, mod_edusharing_get_auth_key()));
    mcrypt_generic_deinit($res);
    $url .= '&u='. rawurlencode($u);

    return $url;
}

function mod_edusharing_get_signature($data) {

    $appproperties = json_decode(get_config('edusharing', 'appProperties'));
    $privkey = $appproperties->private_key;
    $pkeyid = openssl_get_privatekey($privkey);
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

    $mylang = 'en_EN';

    if (isset($USER->lang)) {
        switch(strtolower(substr($USER->lang, 0, 2))) {
            case 'en':
                $mylang = 'en_EN';
            break;
            default:
                $mylang = 'de_DE';
        }
    }
    return $mylang;
}

