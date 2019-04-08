<?php
defined('MOODLE_INTERNAL') || die();

class mod_edusharing_observer {

    public static function course_restored(\core\event\course_restored $event) {
        $eventData = $event->get_data();
        $courseId = $eventData['courseid'];
        mod_edusharing_restorehelper::edusharing_convert_inline_objects($courseId);
    }
}
