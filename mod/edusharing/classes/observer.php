<?php
defined('MOODLE_INTERNAL') || die();

class mod_edusharing_observer {

    /*
     * delete edu-sharing record and usage contained in deleted module
     *
     * */
    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        global $DB;
        $data = $event->get_data();
        $objectid = $data['objectid'];
        //delete es-activities in course-modules
        $eduObjects = $DB -> get_records('edusharing', array('module_id' => $objectid));
        foreach($eduObjects as $object) {
            edusharing_delete_instance($object['id']);
        }
        //delete es-activities in course-sections
        $eduObjects = $DB -> get_records('edusharing', array('section_id' => $objectid));
        foreach($eduObjects as $object) {
            edusharing_delete_instance($object['id']);
        }
    }

    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;
        $data = $event->get_data();
        $module = $DB->get_record($data['other']['modulename'],array('id' =>$data['other']['instanceid']));
        $text = $module->intro;
        $id_type = 'module_id';

        if(!set_module_id_in_db($text, $data, $id_type)){
            error_log('course_module_created: could not set module_id');
        }
        //error_log('course_module_created: '.$module->intro);
    }

    public static function course_module_updated(\core\event\course_module_updated $event) {
        global $DB;
        $data = $event->get_data();
        $module = $DB->get_record($data['other']['modulename'],array('id' =>$data['other']['instanceid']));
        $text = $module->intro;
        $id_type = 'module_id';

        if(!set_module_id_in_db($text, $data, $id_type)){
            error_log('course_module_updated: could not set module_id');
        }
        //error_log('course_module_updated: '.$module->intro);
    }

    public static function course_section_created(\core\event\course_section_created $event) {
        global $DB;
        $data = $event->get_data();
        $module = $DB->get_record('course_sections',array('id' =>$data['objectid']));
        $text = $module->summary;
        $id_type = 'section_id';

        if(!set_module_id_in_db($text, $data, $id_type)){
            error_log('course_section_created: could not set module_id');
        }
        //error_log('course_section_created: '.$module->summary);
    }

    public static function course_section_updated(\core\event\course_section_updated $event) {
        global $DB;
        $data = $event->get_data();
        $module = $DB->get_record('course_sections',array('id' =>$data['objectid']));
        $text = $module->summary;
        $id_type = 'section_id';

        if(!set_module_id_in_db($text, $data, $id_type)){
            error_log('course_section_updated: could not set module_id');
        }
        //error_log('course_section_updated: '.$module->summary);
    }


    /*
     * delete edu-sharing record and usage contained in deleted course (wysiwyg)
     *
     * triggered immediately on deletion
     *
     * */
    public static function course_deleted(\core\event\course_deleted $event) {
        global $DB;
        $data = $event->get_data();
        $objectid = $data['objectid'];
        $eduObjects = $DB -> get_records('edusharing', array('course_id' => $objectid));
        foreach($eduObjects as $object) {
            edusharing_delete_instance($object['id']);
        }
    }


    public static function course_restored(\core\event\course_restored $event) {
        $eventData = $event->get_data();
        $courseId = $eventData['courseid'];
        mod_edusharing_restorehelper::edusharing_convert_inline_objects($courseId);
    }
}

function set_module_id_in_db($text, $data, $id_type)
{
    global $DB;
    preg_match_all('#<img(.*)class="(.*)edusharing_atto(.*)"(.*)>#Umsi', $text, $matchesimg_atto,
        PREG_PATTERN_ORDER);
    preg_match_all('#<a(.*)class="(.*)edusharing_atto(.*)">(.*)</a>#Umsi', $text, $matchesa_atto,
        PREG_PATTERN_ORDER);
    $matches_atto = array_merge($matchesimg_atto[0], $matchesa_atto[0]);

    if (!empty($matches_atto)) {
        foreach ($matches_atto as $match) {
            $resourceId = '';
            if (($pos = strpos($match, "resourceId=")) !== FALSE) {
                $resourceId = substr($match, $pos + 11);
                $resourceId = substr($resourceId, 0, strpos($resourceId, "&"));
            }
            $DB->set_field('edusharing', $id_type, $data['objectid'], array('id' => $resourceId));
        }
    }
    return true;
}

