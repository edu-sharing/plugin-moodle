<?php
/**
 * Structure step to restore one edusharing activity
 */


require_once(dirname(__FILE__).'/../../lib.php');
require_once(dirname(__FILE__).'/../../lib/cclib.php');
require_once(dirname(__FILE__).'/../../classes/restorehelper.php');


class restore_edusharing_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('edusharing', '/activity/edusharing');

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_edusharing($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // insert the edusharing record
        $newitemid = $DB->insert_record('edusharing', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
        mod_edusharing_restorehelper::edusharing_add_usage($data, $newitemid);

    }

    protected function after_execute() {

    }
}
