<?php

/**
 * Define all the backup steps that will be used by the backup_choice_activity_task
 */
/**
 * Define the complete choice structure for backup, with file and id annotations
 */
class backup_edusharing_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $edusharing = new backup_nested_element('edusharing', array('id'), array(
            'course', 'name', 'intro', 'introformat',
            'timecreated', 'timemodified', 'object_url', 'object_version', 'force_download', 'popup_window',
            'show_course_blocks', 'show_directory_links', 'show_menu_bar', 'show_location_bar', 'show_tool_bar',
            'show_status_bar', 'window_allow_resize', 'window_allow_scroll', 'window_width', 'window_height',
            'window_float'));

        $edusharing->set_source_table('edusharing', array('id' => backup::VAR_ACTIVITYID));

        // Build the tree

        // Define sources

        // Define id annotations

        // Define file annotations

        // Return the root element (choice), wrapped into standard activity structure
        return $this->prepare_activity_structure($edusharing);

    }
}
