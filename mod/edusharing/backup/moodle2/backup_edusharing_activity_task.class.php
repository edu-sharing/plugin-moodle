<?php

require_once($CFG->dirroot . '/mod/edusharing/backup/moodle2/backup_edusharing_stepslib.php'); // Because it exists (must)
require_once($CFG->dirroot . '/mod/edusharing/backup/moodle2/backup_edusharing_settingslib.php'); // Because it exists (optional)

/**
 * choice backup task that provides all the settings and steps to perform one
 * complete backup of the activity
 */
class backup_edusharing_activity_task extends backup_activity_task {

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
        // Choice only has one structure step
        $this->add_step(new backup_edusharing_activity_structure_step('edusharing_structure', 'edusharing.xml'));
    }

    /**
     * Code the transformations to perform in the activity in
     * order to get transportable (encoded) links
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot,"/");

        // Link to the list of edusharing
        $search="/(".$base."\/mod\/edusharing\/index.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@EDUSHARINGINDEX*$2@$', $content);

        // Link to edusharing view by moduleid
        $search="/(".$base."\/mod\/edusharing\/view.php\?id\=)([0-9]+)/";
        $content= preg_replace($search, '$@EDUSHARINGVIEWBYID*$2@$', $content);

        return $content;
    }


}
