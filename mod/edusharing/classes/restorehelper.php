<?php
defined('MOODLE_INTERNAL') || die();

class mod_edusharing_restorehelper {

    public static function edusharing_convert_inline_objects($courseId) {
        global $DB;
        $sections = $DB -> get_records('course_sections', array('course' => $courseId));
        foreach ($sections as $section) {
            self::edusharing_restore_objects($section);
        }
        rebuild_course_cache($courseId);
    }

    public static function edusharing_restore_objects($section) {
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
                    $section -> summary = str_replace($match, self::edusharing_convert_object($match, $section), $section -> summary);
                    $DB -> update_record('course_sections', $section);
                }
            }
        } catch (Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
        }
    }

    public static function edusharing_convert_object($object, $section) {
        global $DB;
        $doc = new DOMDocument();
        $doc->loadHTML($object, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

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

        if($id) {
            $usage = self::edusharing_add_usage($edusharing, $id);
        }

        if($usage) {
            $node->setAttribute('es:resource_id', $id);
            $prewUrl = $node -> getAttribute('src');
            if(!empty($prewUrl)) {
                $prewUrl = explode('resourceId=', $prewUrl)[0];
                $prewUrl .= 'resourceId=' . $id;
                $node -> setAttribute('src', $prewUrl);
            }
        } else {
            $DB->delete_records('edusharing', array('id' => $id));
            return $object;
        }
        return $doc -> saveHTML();

    }

    public static function edusharing_add_usage($data, $newitemid) {
        global $USER;
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

        return true;
    }


}
