<?php
/**
 * edusharing restore task that provides all the settings and steps to perform one
 * complete restore of the activity
 */

require_once($CFG->dirroot . '/mod/edusharing/backup/moodle2/restore_edusharing_stepslib.php'); // Because it exists (must)

class restore_edusharing_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // edusharing only has one structure step
        $this->add_step(new restore_edusharing_activity_structure_step('edusharing_structure', 'edusharing.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    static public function define_decode_contents() {
        $contents = array();

        $contents[] = new restore_decode_content('edusharing', array('intro'), 'edusharing');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    static public function define_decode_rules() {
        $rules = array();

        $rules[] = new restore_decode_rule('EDUSHARINGVIEWBYID', '/mod/edusharing/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('EDUSHARINGINDEX', '/mod/edusharing/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * edusharing logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    static public function define_restore_log_rules() {
        $rules = array();

        $rules[] = new restore_log_rule('edusharing', 'add', 'view.php?id={course_module}', '{edusharing}');
        $rules[] = new restore_log_rule('edusharing', 'update', 'view.php?id={course_module}', '{edusharing}');
        $rules[] = new restore_log_rule('edusharing', 'view', 'view.php?id={course_module}', '{edusharing}');
        $rules[] = new restore_log_rule('edusharing', 'choose', 'view.php?id={course_module}', '{edusharing}');
        $rules[] = new restore_log_rule('edusharing', 'choose again', 'view.php?id={course_module}', '{edusharing}');
        $rules[] = new restore_log_rule('edusharing', 'report', 'report.php?id={course_module}', '{edusharing}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note this rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    static public function define_restore_log_rules_for_course() {
        $rules = array();

        // Fix old wrong uses (missing extension)
        $rules[] = new restore_log_rule('edusharing', 'view all', 'index?id={course}', null,
            null, null, 'index.php?id={course}');
        $rules[] = new restore_log_rule('edusharing', 'view all', 'index.php?id={course}', null);

        return $rules;
    }

    public function after_restore() {
        // Do something at end of restore
    }

}
