<?php
/**
 * Structure step to restore one edusharing activity
 */


require_once(dirname(__FILE__).'/../../lib.php');
require_once(dirname(__FILE__).'/../../lib/cclib.php');

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

        $this->edusharing_add_usage($data, $newitemid);

        $this->edusharing_convert_inline_objects($data);
    }

    protected function edusharing_convert_inline_objects($data) {
        global $DB;
        $sections = $DB -> get_records('course_sections', array('course' => $data -> course));
        foreach ($sections as $section) {
            $this->edusharing_restore_objects($section, $data);
        }
    }

    public function edusharing_restore_objects($section, $data) {
        global $DB;
        try {

            if (strpos($section -> summary, 'es:resource_id') === false) {
                return;
            }

            // Ensure that user exists in repository.
            if (isloggedin()) {
                $ccauth = new mod_edusharing_web_service_factory();
                $ccauth->edusharing_authentication_get_ticket();
            }

            preg_match_all('#<img(.*)es:resource_id(.*)>#Umsi', $section -> summary, $matchesimg,
                PREG_PATTERN_ORDER);
            preg_match_all('#<a(.*)es:resource_id(.*)>(.*)</a>#Umsi', $section -> summary, $matchesa,
                PREG_PATTERN_ORDER);
            $matches = array_merge($matchesimg[0], $matchesa[0]);
            if (!empty($matches)) {
                foreach ($matches as $match) {
                    if (strpos($match, 'es:restored') !== false)
                        continue;
                    $section -> summary = str_replace($match, $this->edusharing_convert_object($match, $section), $section -> summary);
                    $DB -> update_record('course_sections', $section);
                }
            }
        } catch (Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
        }
    }

    protected function edusharing_convert_object($object, $section) {
        global $DB;
        $doc = new DOMDocument();
        $doc->loadHTML($object);

        $node = $doc->getElementsByTagName('a')->item(0);
        if (empty($node)) {
            $node = $doc->getElementsByTagName('img')->item(0);
        }
        if (empty($node)) {
            trigger_error(get_string('error_loading_node', 'filter_edusharing'), E_USER_WARNING);
            return false;
        }

        $edusharing = new stdClass();
        $edusharing -> course = $section -> course;
        $edusharing -> name = $node->getAttribute('alt');
        $edusharing -> introformat = 0;
        $edusharing -> object_url = $node->getAttribute('es:object_url');
        $edusharing -> object_version = $node->getAttribute('es:window_version');
        $edusharing -> timecreated = time();
        $edusharing -> timemodified = time();

        $id = $DB -> insert_record('edusharing', $edusharing);

        if($id)
            $usage = $this->edusharing_add_usage($edusharing, $id);
        if($usage) {
            $node->setAttribute('es:resource_id', $id);
            $node->setAttribute('es:restored', time());
        } else {
            $DB->delete_records('edusharing', array('id' => $id));
        }
        return $doc -> saveHTML();

    }

    protected function edusharing_add_usage($data, $newitemid) {
        global $USER;
        try {
            $client = new mod_edusharing_sig_soap_client(get_config('edusharing', 'repository_usagewebservice_wsdl'), array());
            $xml = edusharing_get_usage_xml($data);
            $params = array(
                "eduRef" => $data->object_url,
                "user" => edusharing_get_auth_key(),
                "lmsId" => get_config('edusharing', 'application_appid'),
                "courseId" => $data->course,
                "userMail" => $USER->email,
                "fromUsed" => '2002-05-30T09:00:00',
                "toUsed" => '2222-05-30T09:00:00',
                "distinctPersons" => '0',
                "version" => $data->object_version,
                "resourceId" => $newitemid,
                "xmlParams" => $xml,
            );
            $client->setUsage($params);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    protected function after_execute() {

    }
}
