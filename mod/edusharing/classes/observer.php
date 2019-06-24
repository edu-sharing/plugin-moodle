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
        $eduObjects = $DB -> get_records('edusharing', array('module_id' => $objectid));
        foreach($eduObjects as $object) {
            edusharing_delete_instance($object['id']);
        }
    }

    public static function course_module_created(\core\event\course_module_created $event) {
        global $DB;

        $descr = $event->get_data();
        $module = $DB->get_record($descr['other']['modulename'],array('id' =>$descr['other']['instanceid']));

        $text = $module->intro;

        preg_match_all('#<img(.*)class="(.*)edusharing_atto(.*)"(.*)>#Umsi', $text, $matchesimg_atto,
            PREG_PATTERN_ORDER);
        preg_match_all('#<a(.*)class="(.*)edusharing_atto(.*)">(.*)</a>#Umsi', $text, $matchesa_atto,
            PREG_PATTERN_ORDER);
        $matches_atto = array_merge($matchesimg_atto[0], $matchesa_atto[0]);

        if (!empty($matches_atto)) {

            foreach ($matches_atto as $match) {
                $resourceId = '';
                if (($pos = strpos($match, "resourceId=")) !== FALSE) {
                    $resourceId = substr($match, $pos+11);
                    $resourceId = substr($resourceId, 0, strpos($resourceId, "&"));
                }

                $DB->set_field('edusharing', 'module_id', $descr['objectid'], array('id' => $resourceId));

                error_log('resourceId: ' . $resourceId );
            }
        }

        error_log('course_module_created: '.$module->intro);
    }

    /*
     *
     * @todo implement course_module_updated (same function as course_module_created)
     *
     * */


    /*
     * @todo
     *
     * course section (+ course??) delete parse content for
     *
     * \core\event\course_section_deleted
     *

     *
     *
     * */


    /*
     * delete edu-sharing record and usage contained in deleted course (wysiwyg)
     *
     * */
    public static function course_deleted(\core\event\course_deleted $event) {


        //triggered immediately on deletion not by cron ?!?!?!?!??!?!?!!?? why????????? What to do??????????????????????


        global $DB;
        $data = $event->get_data();
        var_dump($data);
        echo '#########################################';
        die();
    }

}
