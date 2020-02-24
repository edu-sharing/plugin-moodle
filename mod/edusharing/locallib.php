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

    // Set by external sso script.
    if (isset($SESSION -> edusharing_sso) && !empty($SESSION -> edusharing_sso)) {
        $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
        return $SESSION -> edusharing_sso[$eduauthparamnameuserid];
    }

    $guestoption = get_config('edusharing', 'edu_guest_option');
    if (!empty($guestoption)) {
        $guestid = get_config('edusharing', 'edu_guest_guest_id');
        if (empty($guestid)) {
            $guestid = 'esguest';
        }
        return $guestid;
    }

    $eduauthkey = get_config('edusharing', 'EDU_AUTH_KEY');

    if($eduauthkey == 'id')
        return $USER->id;
    if($eduauthkey == 'idnumber')
        return $USER->idnumber;
    if($eduauthkey == 'email')
        return $USER->email;
    if(isset($USER->profile[$eduauthkey]))
        return $USER->profile[$eduauthkey];
    return $USER->username;
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
        $url .= '&role=' . urlencode($role -> shortname);
    }

    $url .= '&display='.urlencode($displaymode);
    
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

/**
 * Fill in the metadata from the repository
 * Returns true on success
 *
 * @param string $metadataurl
 * @return bool
 */
function edusharing_import_metadata($metadataurl){
    global $CFG;
    try {

        $xml = new DOMDocument();

        libxml_use_internal_errors(true);

        $curlhandle = curl_init($metadataurl);
        curl_setopt($curlhandle, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curlhandle, CURLOPT_HEADER, 0);
        curl_setopt($curlhandle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curlhandle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curlhandle, CURLOPT_SSL_VERIFYHOST, false);
        $properties = curl_exec($curlhandle);
        if ($xml->loadXML($properties) == false) {
            echo ('<p style="background: #FF8170">could not load ' . $metadataurl .
                    ' please check url') . "<br></p>";
            echo get_form($metadataurl);
            return false;
        }
        curl_close($curlhandle);
        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $entrys = $xml->getElementsByTagName('entry');
        foreach ($entrys as $entry) {
            set_config('repository_'.$entry->getAttribute('key'), $entry->nodeValue, 'edusharing');
        }

        require_once(dirname(__FILE__) . '/AppPropertyHelper.php');
        $modedusharingapppropertyhelper = new mod_edusharing_app_property_helper();
        $sslkeypair = $modedusharingapppropertyhelper->edusharing_get_ssl_keypair();

        $host = $_SERVER['SERVER_ADDR'];
        if(empty($host))
            $host = gethostbyname($_SERVER['SERVER_NAME']);

        set_config('application_host', $host, 'edusharing');
        set_config('application_appid', uniqid('moodle_'), 'edusharing');
        set_config('application_type', 'LMS', 'edusharing');
        set_config('application_homerepid', get_config('edusharing', 'repository_appid'), 'edusharing');
        set_config('application_cc_gui_url', get_config('edusharing', 'repository_clientprotocol') . '://' .
            get_config('edusharing', 'repository_domain') . ':' .
            get_config('edusharing', 'repository_clientport') . '/edu-sharing/', 'edusharing');
        set_config('application_private_key', $sslkeypair['privateKey'], 'edusharing');
        set_config('application_public_key', $sslkeypair['publicKey'], 'edusharing');
        set_config('application_blowfishkey', 'thetestkey', 'edusharing');
        set_config('application_blowfishiv', 'initvect', 'edusharing');

        set_config('EDU_AUTH_KEY', 'username', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_USERID', 'userid', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_LASTNAME', 'lastname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', 'firstname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_EMAIL', 'email', 'edusharing');
        set_config('EDU_AUTH_AFFILIATION', $CFG->siteidentifier, 'edusharing');
        set_config('EDU_AUTH_AFFILIATION_NAME', $CFG->siteidentifier, 'edusharing');

        if (empty($sslkeypair['privateKey'])) {
            echo '<h3 class="edu_error">Generating of SSL keys failed. Please check your configuration.</h3>';
        } else {
            echo '<h3 class="edu_success">Import successful.</h3>';
        }
        return true;
    } catch (Exception $e) {
        echo $e->getMessage();
        return false;
    }
}

function callRepoAPI($method, $url, $ticket=NULL, $auth=NULL, $data=NULL){
    $curl = curl_init();
    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($data){
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
            if ($data){
                $fields = array(
                    'file[0]' => new CURLFile($data, 'text/xml', 'metadata.xml')
                );
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            }
            break;
        default:
            if ($data){
                $url = sprintf("%s?%s", $url, http_build_query($data));
            }
    }
    // OPTIONS:
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_USERPWD, $auth);
    if (empty($ticket)){
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json',
        ));
    }else{
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Authorization: EDU-TICKET '.$ticket,
            'Accept: application/json',
            'Content-Type: application/json',
        ));
    }

    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);

    // EXECUTE:
    try{
        $result = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        //error_log('$httpcode: '.$httpcode);
        if($result === false) {
            trigger_error(curl_error($curl), E_USER_WARNING);
        }
        if ($httpcode === 401){
            $result = json_encode(array('message' => 'Error 401: Unauthorized. Please check your credentials.'));
        }
    } catch (Exception $e) {
        error_log('error: '.$e->getMessage());
        trigger_error($e->getMessage(), E_USER_WARNING);
    }
    //error_log('api called: '.$result);
    if(!$result){
        $result = "Connection Failure";
    }
    curl_close($curl);
    return $result;
}
