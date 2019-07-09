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
 * Library of interface functions and constants for module edusharing
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 * All the edusharing specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */

defined('MOODLE_INTERNAL') || die();

define('EDUSHARING_MODULE_NAME', 'edusharing');
define('EDUSHARING_TABLE', 'edusharing');

define('EDUSHARING_DISPLAY_MODE_DISPLAY', 'window');
define('EDUSHARING_DISPLAY_MODE_INLINE', 'inline');

set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) .'/lib');
require_once(dirname(__FILE__).'/lib/RenderParameter.php');
require_once(dirname(__FILE__).'/lib/cclib.php');
require_once(dirname(__FILE__).'/locallib.php');

/**
 * If you for some reason need to use global variables instead of constants, do not forget to make them
 * global as this file can be included inside a function scope. However, using the global variables
 * at the module level is not a recommended.
 */


/**
 * Module feature detection.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed True if module supports feature, false if not, null if doesn't know
 */
function edusharing_supports($feature) {

    /*
     * ATTENTION: take extra care when modifying switch()-statement as we're
     * using switch()'s fall-through mechanism to group features by true/false.
     */
    switch($feature) {
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_RESOURCE;
            break;
        case FEATURE_MOD_INTRO:
        case FEATURE_BACKUP_MOODLE2:
            return true;
            break;
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_COMPLETION_HAS_RULES:
        case FEATURE_IDNUMBER:
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        case FEATURE_GROUPMEMBERSONLY:
        case FEATURE_MOD_ARCHETYPE:
        case FEATURE_MOD_INTRO:
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
        case FEATURE_COMMENT:
        case FEATURE_RATE:
            return false;
        default:
            return false;
    }

    return null;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $edusharing An object from the form in mod_form.php
 * @return int The id of the newly inserted edusharing record
 */
function edusharing_add_instance(stdClass $edusharing) {

    global $DB, $USER;

    $edusharing->timecreated = time();
    $edusharing->timemodified = time();

    // You may have to add extra stuff in here.
    $edusharing = edusharing_postprocess($edusharing);

    //use simple version handling for atto plugin or legacy code
    if(isset($edusharing -> editor_atto)) {
        //avoid database error
        $edusharing->introformat = 0;
    } else {
        if (isset($edusharing->object_version)) {
            if ($edusharing->object_version == 1) {
                $updateversion = true;
                $edusharing->object_version = '';
            } else {
                $edusharing->object_version = 0;
            }
        } else {

            if (isset($edusharing->window_versionshow) && $edusharing->window_versionshow == 'current') {
                $edusharing->object_version = $edusharing->window_version;
            } else {
                $edusharing->object_version = 0;
            }
        }
    }

    $id = $DB->insert_record(EDUSHARING_TABLE, $edusharing);
    $soapclientparams = array();
    $client = new mod_edusharing_sig_soap_client(get_config('edusharing', 'repository_usagewebservice_wsdl'), $soapclientparams);
    $xml = edusharing_get_usage_xml($edusharing);
    try {
        $params = array(
            "eduRef"  => $edusharing->object_url,
            "user"  => edusharing_get_auth_key(),
            "lmsId"  => get_config('edusharing', 'application_appid'),
            "courseId"  => $edusharing->course,
            "userMail"  => $USER->email,
            "fromUsed"  => '2002-05-30T09:00:00',
            "toUsed"  => '2222-05-30T09:00:00',
            "distinctPersons"  => '0',
            "version"  => $edusharing->object_version,
            "resourceId"  => $id,
            "xmlParams"  => $xml,
        );
        $setusage = $client->setUsage($params);

        if (isset($updateversion) && $updateversion === true) {
            $edusharing->object_version = $setusage->setUsageReturn->usageVersion;
            $edusharing->id = $id;
            $DB->update_record(EDUSHARING_TABLE, $edusharing);
        }

    } catch (Exception $e) {
        $DB->delete_records(EDUSHARING_TABLE, array('id'  => $id));
        error_log(print_r($e, true));
        trigger_error($e->getMessage());
        return false;
    }

    return $id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $edusharing An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function edusharing_update_instance(stdClass $edusharing) {

    global $CFG, $COURSE, $DB, $SESSION, $USER;

    // FIX: when editing a moodle-course-module the $edusharing->id will be named $edusharing->instance
    if ( ! empty($edusharing->instance) ) {
        $edusharing->id = $edusharing->instance;
    }

    $edusharing->timemodified = time();

    // Load previous state.
    $memento = $DB->get_record(EDUSHARING_TABLE, array('id'  => $edusharing->id));
    if ( ! $memento ) {
        throw new Exception(get_string('error_loading_memento', 'edusharing'));
    }

    // You may have to add extra stuff in here.
    $edusharing = edusharing_postprocess($edusharing);

    $xml = edusharing_get_usage_xml($edusharing);

    try {
        $connectionurl = get_config('edusharing', 'repository_usagewebservice_wsdl');
        if (!$connectionurl) {
            trigger_error(get_string('error_missing_usagewsdl', 'edusharing'), E_USER_WARNING);
        }

        $client = new mod_edusharing_sig_soap_client($connectionurl, array());

        $params = array(
            "eduRef"  => $edusharing->object_url,
            "user"  => edusharing_get_auth_key(),
            "lmsId"  => get_config('edusharing', 'application_appid'),
            "courseId"  => $edusharing->course,
            "userMail"  => $USER->email,
            "fromUsed"  => '2002-05-30T09:00:00',
            "toUsed"  => '2222-05-30T09:00:00',
            "distinctPersons"  => '0',
            "version"  => $memento->object_version,
            "resourceId"  => $edusharing->id,
            "xmlParams"  => $xml,
        );

        $setusage = $client->setUsage($params);
        $edusharing->object_version = $memento->object_version;
        // Throws exception on error, so no further checking required.
        $DB->update_record(EDUSHARING_TABLE, $edusharing);
    } catch (SoapFault $exception) {
        // Roll back.
        $DB->update_record(EDUSHARING_TABLE, $memento);

        trigger_error($exception->getMessage(), E_USER_WARNING);

        return false;
    }

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function edusharing_delete_instance($id) {
    global $DB;
    global $CFG;
    global $COURSE;

    error_log('edusharing_delete_instance id: '.$id);
    echo 'edusharing_delete_instance';

    // Load from DATABASE to get object-data for repository-operations.
    if (! $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id'  => $id))) {
        throw new Exception(get_string('error_load_resource', 'edusharing'));
    }

    try {

        $connectionurl = get_config('edusharing', 'repository_usagewebservice_wsdl');
        if ( ! $connectionurl ) {
            throw new Exception(get_string('error_missing_usagewsdl', 'edusharing'));
        }

        $ccwsusage = new mod_edusharing_sig_soap_client($connectionurl, array());

        $params = array(
            'eduRef'  => $edusharing->object_url,
            'user'  => edusharing_get_auth_key(),
            'lmsId'  => get_config('edusharing', 'application_appid'),
            'courseId'  => $edusharing->course,
            'resourceId'  => $edusharing->id
        );

        $ccwsusage->deleteUsage($params);
        error_log($edusharing->id . ' usage deleted :)');

    } catch (Exception $exception) {
        trigger_error($exception->getMessage(), E_USER_WARNING);
    }

    // Usage is removed so it can be deleted from DATABASE .
    $DB->delete_records(EDUSHARING_TABLE, array('id'  => $edusharing->id));

    return true;

}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $edusharing
 *
 * @return stdClass
 */
function edusharing_user_outline($course, $user, $mod, $edusharing) {

    $return = new stdClass;

    $return->time = time();
    $return->info = 'edusharing_user_outline() - edu-sharing activity outline.';

    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @param object $course
 * @param object $user
 * @param object $mod
 * @param object $edusharing
 *
 * @return boolean
 */
function edusharing_user_complete($course, $user, $mod, $edusharing) {
    return true;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in edusharing activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @param object $course
 * @param object $isteacher
 * @param object $timestart
 *
 * @return boolean
 */
function edusharing_print_recent_activity($course, $isteacher, $timestart) {
    return false; // True if anything was printed, otherwise false
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 **/
function edusharing_cron() {
    return true;
}

/**
 * Must return an array of users who are participants for a given instance
 * of edusharing. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $edusharingid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function edusharing_get_participants($edusharingid) {
    return false;
}

/**
 * This function returns if a scale is being used by one edusharing
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param int $edusharingid ID of an instance of this module
 * @param int $scaleid
 * @return mixed
 */
function edusharing_scale_used($edusharingid, $scaleid) {
    global $DB;

    $return = false;
    return $return;
}

/**
 * Checks if scale is being used by any instance of edusharing.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 * @param int $scaleid
 * @return boolean True if the scale is used by any edusharing
 */
function edusharing_scale_used_anywhere($scaleid) {
    global $DB;

    return false;
}

/**
 * Execute post-install actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function edusharing_install() {
    return true;
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function edusharing_uninstall() {
    return true;
}

/**
 * Moodle will cache the outpu of this method, so it gets only called after
 * adding or updating an edu-sharing-resource, NOT every time the course
 * is shown.
 *
 * @param stdClass $coursemodule
 *
 * @return stdClass
 */
function edusharing_get_coursemodule_info($coursemodule) {
    global $CFG;
    global $DB;

    $info = new cached_cm_info();

    $resource = $DB->get_record(EDUSHARING_TABLE, array('id'  => $coursemodule->instance));
    if ( ! $resource ) {
        trigger_error(get_string('error_load_resource', 'edusharing'), E_USER_WARNING);
    }

    if (!empty($resource->popup_window)) {
        $info->onclick = 'this.target=\'_blank\';';
    }

    return $info;
}

/**
 * Normalize form-values ...
 *
 * @param stdclass $edusharing
 *
 * @return stdClass
 *
 */
function edusharing_postprocess($edusharing) {
    global $COURSE;

    if ( empty($edusharing->timecreated) ) {
        $edusharing->timecreated = time();
    }

    $edusharing->timeupdated = time();

    if (!empty($edusharing->force_download)) {
        $edusharing->force_download = 1;
        $edusharing->popup_window = 0;
    } else if (!empty($edusharing->popup_window)) {
        $edusharing->force_download = 0;
        $edusharing->options = '';
    } else {
        if (empty($edusharing->blockdisplay)) {
            $edusharing->options = '';
        }

        $edusharing->popup_window = '';
    }

    $edusharing->tracking = empty($edusharing->tracking) ? 0 : $edusharing->tracking;

    if ( ! $edusharing->course ) {
        $edusharing->course = $COURSE->id;
    }


    return $edusharing;
}

/**
 * Get the object-id from object-url.
 * E.g. "abc-123-xyz-456789" for "ccrep://homeRepository/abc-123-xyz-456789"
 *
 * @param string $objecturl
 * @throws Exception
 * @return string
 */
function edusharing_get_object_id_from_url($objecturl) {
    $objectid = parse_url($objecturl, PHP_URL_PATH);
    if ( ! $objectid ) {
        trigger_error(get_string('error_get_object_id_from_url', 'edusharing'), E_USER_WARNING);
        return false;
    }

    $objectid = str_replace('/', '', $objectid);

    return $objectid;
}

/**
 * Get the repository-id from object-url.
 * E.g. "homeRepository" for "ccrep://homeRepository/abc-123-xyz-456789"
 *
 * @param string $objecturl
 * @throws Exception
 * @return string
 */
function edusharing_get_repository_id_from_url($objecturl) {
    $repid = parse_url($objecturl, PHP_URL_HOST);
    if ( ! $repid ) {
        throw new Exception(get_string('error_get_repository_id_from_url', 'edusharing'));
    }

    return $repid;
}

/**
 * Get additional usage information
 *
 * @param stdClass $edusharing
 * @return string
 */
function edusharing_get_usage_xml($edusharing) {
    global $DB;

    $course = $DB->get_record('course', array('id'  => $edusharing->course));
    $category = $DB->get_record('course_categories', array('id'  => $course->category));
    $site = get_site();

    $data4xml = array("usage");

    $data4xml[1]["general"]['referencedInName'] = $course->fullname;
    $data4xml[1]["general"]['referencedInType'] = 'course';
    $data4xml[1]["general"]['referencedInInstance'] = $site->fullname;

    $data4xml[1]["specific"]['type'] = 'moodle';
    $data4xml[1]["specific"]['courseId'] = $edusharing->course;
    $data4xml[1]["specific"]['courseFullname'] = $course->fullname;
    $data4xml[1]["specific"]['courseShortname'] = $course->shortname;
    $data4xml[1]["specific"]['courseSummary'] = $course->summary;
    $data4xml[1]["specific"]['categoryId'] = $course->category;
    $data4xml[1]["specific"]['categoryName'] = $category->name;
    $myxml  = new mod_edusharing_render_parameter();
    $xml = $myxml->edusharing_get_xml($data4xml);
    return $xml;
}

/**
 * Hook called before we delete a course module.
 *
 * @param \stdClass $cm The course module record.
 */
function edusharing_pre_course_module_delete($cm) {
    //$descr = $cm->get_description();
    //error_log('edusharing_pre_course_module_delete: '.print_r($cm, true));
}

function edusharing_course_module_background_deletion_recommended() {
    return false;
}

function edusharing_pre_block_delete($cm) {
    //echo 'edusharing_pre_block_delete';
    //error_log('edusharing_pre_block_delete');
}
