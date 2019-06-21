<?php
defined('MOODLE_INTERNAL') || die();

class mod_edusharing_observer {


    public static function course_module_deleted(\core\event\course_module_deleted $event) {
        $descr = $event->get_description();
        //echo "<script>console.log( 'course_module_created: ".$descr."' );</script>";
        //echo '<script>';
        //echo 'console.log('. json_encode( $descr ) .')';
        //echo '</script>';
        error_log($descr);
       //var_dump($event);die();
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
                //$text = str_replace($match, $this->filter_edusharing_convert_object($match), $text, $count);

                //$DB->set_field('edusharing', 'moduleID', $descr['other']['instanceid'], array('id' => $descr['objectid']));
                $resourceId = '';
                if (($pos = strpos($match, "resourceId=")) !== FALSE) {
                    $resourceId = substr($match, $pos+11);
                    $resourceId = substr($resourceId, 0, strpos($resourceId, "&"));
                }

                $DB->set_field('edusharing', 'moduleID', $descr['other']['instanceid'], array('id' => $resourceId));

                error_log('resourceId: ' . $resourceId );
            }
        }

        error_log('course_module_created: '.$module->intro);
    }


    public static function course_module_updated(\core\event\course_module_updated $event) {
        $descr = $event->get_description();
        error_log($descr);
    }

    public static function course_updated(\core\event\course_updated $event) {
        $descr = $event->get_description();
        error_log($descr);
    }

    public static function course_created(\core\event\course_created $event) {
        $descr = $event->get_description();
        error_log($descr);
    }

    public static function user_signup(\core\event\user_created $event) {
        $descr = $event->get_description();
        error_log($descr);
    }
}
