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
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

/**
 * Get the parameter for authentication
 * @return string
 */
function edusharing_get_auth_key() {

    global $USER, $SESSION;

    $id = '';
    $guestoption = get_config('edusharing', 'edu_guest_option');

    switch(true) {
        case (isset($SESSION -> edusharing_sso) && !empty($SESSION -> edusharing_sso)):
            $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
            $id = $SESSION -> edusharing_sso[$eduauthparamnameuserid];
            var_dump('tosrti');
            break;
        case ($guestoption == 1):
            $id = get_config('edusharing', 'edu_guest_guest_id');
            if(empty($id))
                $id = 'esguest';
            break;
        default:
            $eduauthkey = get_config('edusharing', 'EDU_AUTH_KEY');
            if($eduauthkey == 'id')
                $id = $USER->id;
            if($eduauthkey == 'idnumber')
                $id = $USER->idnumber;
            if($eduauthkey == 'email')
                $id = $USER->email;
            if(isset($USER->profile[$eduauthkey]))
                $id = $USER->profile[$eduauthkey];
            if(empty($id))
                $id = $USER->username;
    }

    if(get_config('edusharing', 'EDU_AUTH_OBFUSCATE_USER') && !$guestoption)
        $id = md5($id);

    return $id;
}


/**
 * Return data for authByTrustedApp
 *
 * @return array
 */
function edusharing_get_auth_data() {

    global $USER, $CFG, $SESSION;

    // Set by external sso script.
    if (isset($SESSION -> edusharing_sso) && !empty($SESSION -> edusharing_sso)) {
        $authparams = array();
        foreach ($SESSION -> edusharing_sso as $key => $value) {
            $authparams[] = array('key'  => $key, 'value'  => $value);
        }
    } else {
        // Keep defaults in sync with settings.php.
        $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        if (empty($eduauthparamnameuserid)) {
            $eduauthparamnameuserid = '';
        }

        $eduauthparamnamelastname = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_LASTNAME');
        if (empty($eduauthparamnamelastname)) {
            $eduauthparamnamelastname = '';
        }

        $eduauthparamnamefirstname = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_FIRSTNAME');
        if (empty($eduauthparamnamefirstname)) {
            $eduauthparamnamefirstname = '';
        }

        $eduauthparamnameemail = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_EMAIL');
        if (empty($eduauthparamnameemail)) {
            $eduauthparamnameemail = '';
        }

        $eduauthaffiliation = get_config('edusharing', 'EDU_AUTH_AFFILIATION');

        $eduauthaffiliationname = get_config('edusharing', 'EDU_AUTH_AFFILIATION_NAME');

        $guestoption = get_config('edusharing', 'edu_guest_option');
        if (!empty($guestoption)) {
            $guestid = get_config('edusharing', 'edu_guest_guest_id');
            if (empty($guestid)) {
                $guestid = 'esguest';
            }

            $authparams = array(
                array('key'  => $eduauthparamnameuserid, 'value'  => $guestid),
                array('key'  => $eduauthparamnamelastname, 'value'  => ''),
                array('key'  => $eduauthparamnamefirstname, 'value'  => ''),
                array('key'  => $eduauthparamnameemail, 'value'  => ''),
                array('key'  => 'affiliation', 'value'  => $eduauthaffiliation),
                array('key'  => 'affiliationname', 'value' => $eduauthaffiliationname)
            );
        } else {
            $authparams = array(
                array('key'  => $eduauthparamnameuserid, 'value'  => edusharing_get_auth_key()),
                array('key'  => $eduauthparamnamelastname, 'value'  => $USER->lastname),
                array('key'  => $eduauthparamnamefirstname, 'value'  => $USER->firstname),
                array('key'  => $eduauthparamnameemail, 'value'  => $USER->email),
                array('key'  => 'affiliation', 'value'  => $eduauthaffiliation),
                array('key'  => 'affiliationname', 'value' => $eduauthaffiliationname)
            );
        }
    }

    if (get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS') == 'yes' ||
            get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS') == '1') {
        $authparams[] = array('key'  => 'globalgroups', 'value'  => edusharing_get_user_cohorts());
    }
    return $authparams;
}

/**
 * Get cohorts the user belongs to
 *
 * @return array
 */
function edusharing_get_user_cohorts() {
    global $DB, $USER;
    $ret = array();
    $cohortmemberships = $DB->get_records('cohort_members', array('userid'  => $USER->id));
    if ($cohortmemberships) {
        foreach ($cohortmemberships as $cohortmembership) {
            $cohort = $DB->get_record('cohort', array('id'  => $cohortmembership->cohortid));
            if($cohort->contextid == 1)
                $ret[] = array(
                        'id'  => $cohortmembership->cohortid,
                        'contextid'  => $cohort->contextid,
                        'name'  => $cohort->name,
                        'idnumber'  => $cohort->idnumber
                );
        }
    }
    return json_encode($ret);
}

/**
 * Generate redirection-url
 *
 * @param stdClass $edusharing
 * @param string $displaymode
 *
 * @return string
 */

function edusharing_get_redirect_url(
    stdClass $edusharing,
    $displaymode = EDUSHARING_DISPLAY_MODE_DISPLAY) {
    global $USER;

    $url = get_config('edusharing', 'application_cc_gui_url') . '/renderingproxy';

    $url .= '?app_id='.urlencode(get_config('edusharing', 'application_appid'));

    $url .= '&session='.urlencode(session_id());

    $repid = edusharing_get_repository_id_from_url($edusharing->object_url);
    $url .= '&rep_id='.urlencode($repid);

    $url .= '&obj_id='.urlencode(edusharing_get_object_id_from_url($edusharing->object_url));

    $url .= '&resource_id='.urlencode($edusharing->id);
    $url .= '&course_id='.urlencode($edusharing->course);

    $context = context_course::instance($edusharing->course);
    $roles = get_user_roles($context, $USER->id);
    foreach ($roles as $role) {
        $url .= '&role=' = urlencode(role_get_name($role, $context));
    }

    $url .= '&display='.urlencode($displaymode);

    $url .= '&width=' . urlencode($edusharing->window_width);
    $url .= '&height=' . urlencode($edusharing->window_height);
    $url .= '&version=' . urlencode($edusharing->object_version);
    $url .= '&locale=' . urlencode(current_language()); //repository
    $url .= '&language=' . urlencode(current_language()); //rendering service

    if(version_compare(get_config('edusharing', 'repository_version'), '4.1' ) >= 0) {
        $url .= '&u='. rawurlencode(base64_encode(edusharing_encrypt_with_repo_public(edusharing_get_auth_key())));
    } else {
        $eskey = get_config('edusharing', 'application_blowfishkey');
        $esiv = get_config('edusharing', 'application_blowfishiv');
        $res = mcrypt_module_open(MCRYPT_BLOWFISH, '', MCRYPT_MODE_CBC, '');
        mcrypt_generic_init($res, $eskey, $esiv);
        $u = base64_encode(mcrypt_generic($res, edusharing_get_auth_key()));
        mcrypt_generic_deinit($res);
        $url .= '&u=' . rawurlencode($u);
    }

    return $url;
}

/**
 * Generate ssl signature
 *
 * @param string $data
 * @return string
 */
function edusharing_get_signature($data) {
    $privkey = get_config('edusharing', 'application_private_key');
    $pkeyid = openssl_get_privatekey($privkey);
    openssl_sign($data, $signature, $pkeyid);
    $signature = base64_encode($signature);
    openssl_free_key($pkeyid);
    return $signature;
}

/**
 * Return openssl encrypted data
 * Uses repositorys openssl public key
 *
 * @param string $data
 * @return string
 */
function edusharing_encrypt_with_repo_public($data) {
    $crypted = '';
    $key = openssl_get_publickey(get_config('edusharing', 'repository_public_key'));
    openssl_public_encrypt($data ,$crypted, $key);
    if($crypted === false) {
        trigger_error(get_string('error_encrypt_with_repo_public', 'edusharing'), E_USER_WARNING);
        return false;
    }
    return $crypted;
}