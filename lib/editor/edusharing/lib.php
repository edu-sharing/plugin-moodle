<?php
// This file is part of Moodle - http://moodle.org/
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Provide some basic functions for the edu-sharing editor plugin
 *
 * @package    editor_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../tinymce/lib.php');
require_once($CFG->dirroot.'/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot.'/mod/edusharing/lib.php');

/**
 * Provide some basic functions for the edu-sharing editor plugin
 *
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class edusharing_texteditor extends tinymce_texteditor {

    /**
     * The namespace used for edu-sharing's attributes
     *
     * @var string
     */
    const ATTRIBUTE_NAMESPACE_URI = 'http://www.edu-sharing.net/editor/tinymce';

    /**
     * The namespace-prefix used for edu-sharing's attributes
     *
     * @var string
     */
    const ATTRIBUTE_NAMESPACE_PREFIX = 'es';

    /**
     * Get edu-sharing ticket
     *
     * @return string
     */
    protected function editor_edusharing_init_edusharing_ticket() {

        global $SESSION;
        /*
        * Use previously generated ticket if available. Generates conflict if
        * repository-session closes too early.
        */
        if ( ! empty($SESSION->edusharingeditorticket) ) {
            return $SESSION->edusharingeditorticket;
        }

        if ( empty($SESSION->edusharingeditor) ) {
            $SESSION->edusharingeditor = array();
        }

        $repositoryid = get_config('edusharing', 'application_homerepid');

        $ccauth = new mod_edusharing_web_service_factory();
        $edusharingticket = $ccauth->edusharing_authentication_get_ticket();
        if ( ! $edusharingticket ) {
            unset($SESSION->edusharingeditorticket);
            return false;
        }

        // Store ticket in session.
        $SESSION->edusharingeditorticket = $edusharingticket;

        return $edusharingticket;
    }

    /**
     * As edu-sharing cannot be used in every context (like editing a user's
     * profile) we have to detect the current editor-context and decide if
     * edu-sharing is applicable to this context.
     *
     * @param array $options the editor-options from tinymce_texteditor::use_editor()
     *
     * @return bool
     */
    protected function editor_edusharing_is_edusharing_context(array $options) {
        global $COURSE;

        if ( empty($options['context']) ) {
            return false;
        }

        $result = false;
        switch( $options['context']->contextlevel ) {
            case CONTEXT_COURSE:
                $result = true;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    /**
     * Set parameters for tinymce
     * @param int $elementid
     * @param array $options
     *
     * @return array
     *
     * @see tinymce_texteditor::get_init_params()
     */
    protected function get_init_params($elementid, array $options=null) {
        global $CFG;
        global $COURSE;
        global $PAGE;
        global $OUTPUT;

        // retrieve params from default tinymce-editor
        $params = parent::get_init_params($elementid, $options);

        // add edu-sharing functionaliy to tinymce ONLY when course-id available
        if ( $this->editor_edusharing_is_edusharing_context($options) ) {
            $edusharingticket = $this->editor_edusharing_init_edusharing_ticket();

            // register tinymce-plugin but DO NOT try to load it as this already happened
            $params['plugins'] .= ',-edusharing';

            // add tool-button
            if (empty($params['theme_advanced_buttons3_add'])) {
                $params['theme_advanced_buttons3_add'] = '';
            }
            $params['theme_advanced_buttons3_add'] .= ',|,edusharing';

            // additional params required by edu-sharing.net
            empty($params['extended_valid_elements']) ? $params['extended_valid_elements'] = '' : $params['extended_valid_elements'] .= ',';

            $params['extended_valid_elements'] .= 'a[href|data|type|width|height|alt|title|xmlns::'.self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';
            $params['extended_valid_elements'] .= ',object[data|type|width|height|alt|title|xmlns::'.self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::mediatype|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';
            $params['extended_valid_elements'] .= ',img[style|longdesc|usemap|src|border|alt=|title|hspace|vspace|width|height|align|xmlns::'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::mediatype|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.
                                                    self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';

            $params['moodle_wwwroot'] = $CFG->wwwroot;
            $params['edusharing_course_id'] = $COURSE->id;
            $params['edusharing_ticket'] = $edusharingticket;

            $params['edusharing_namespace_uri'] = self::ATTRIBUTE_NAMESPACE_URI;
            $params['edusharing_namespace_prefix'] = self::ATTRIBUTE_NAMESPACE_PREFIX;

            $params['edusharing_dialog_width'] = 550;
            $params['edusharing_dialog_height'] = 400;

            $params['convert_urls'] = false;

            $params['moodle_sesskey'] = sesskey();

            $stringman = get_string_manager();
            $params['edusharing_lang'] = $stringman->load_component_strings('editor_edusharing', current_language());

        }

        return $params;
    }

    /**
     * Prepare tinymce to use edu-sharing plugin
     *
     * @param int $elementid
     * @param array $options
     * @param array $filepickeroptions
     *
     * (non-PHPdoc)
     * @see tinymce_texteditor::use_editor()
     */
    public function use_editor($elementid, array $options=null, $filepickeroptions=null) {
        global $CFG;
        global $COURSE;
        global $PAGE;
        global $OUTPUT;

        // include edu-sharing class
        $PAGE->requires->js('/lib/editor/edusharing/js/edusharing.js');

        // register namespace for custom attributes
        $OUTPUT->htmlattributes('xmlns:'.self::ATTRIBUTE_NAMESPACE_PREFIX.'="'.self::ATTRIBUTE_NAMESPACE_URI.'"');

        // tell tinymce to load plugin from non-standard plugin location
        $PAGE->requires->js_init_code('tinymce.PluginManager.load("edusharing", "' . $CFG->wwwroot . '/lib/editor/edusharing/js/tinymce/plugin/editor_plugin.js?'.
                filemtime($CFG->libdir . '/editor/edusharing/js/tinymce/plugin/editor_plugin.js').'");');

        return parent::use_editor($elementid, $options, $filepickeroptions);
    }

}
