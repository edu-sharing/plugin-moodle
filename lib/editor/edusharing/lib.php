<?php
// This file is part of edu-sharing created by metaVentis GmbH — http://metaventis.com
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    editor
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__).'/../tinymce/lib.php');

require_once($CFG->dirroot.'/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot.'/mod/edusharing/lib.php');

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
     *
     * @return string
     */
    protected function mod_edusharing_init_edusharing_ticket() {
        /*
         * use previously generated ticket if available. Generates conflict if
        * repository-session closes too early.
        */
        if ( ! empty($_SESSION['edusharing']['editor']['ticket']) ) {
            return $_SESSION['edusharing']['editor']['ticket'];
        }

        if ( empty($_SESSION['edusharing']) ) {
            $_SESSION['edusharing'] = array();
        }

        if ( empty($SESSION['edusharing']['editor']) ) {
            $_SESSION['edusharing']['editor'] = array();
        }

        $appproperties = json_decode(get_config('edusharing', 'appProperties'));
        $repositoryid = $appproperties->homerepid;

        $ccauth = new mod_edusharing_web_service_factory();
        $edusharingticket = $ccauth->mod_edusharing_authentication_get_ticket($appproperties->appid);
        if ( ! $edusharingticket ) {
            unset($_SESSION['edusharing']['editor']['ticket']);
            return false;
        }

        // store ticket in session
        $_SESSION['edusharing']['editor']['ticket'] = $edusharingticket;

        return $edusharingticket;
    }

    /**
     * As edu-sharing cannot be used in every context (like editing a user's
     * profile) we have to detect the current editor-context and decide if
     * edu-sharing is applicable to this context.
     *
     * @param $options the editor-options from tinymce_texteditor::use_editor()
     *
     * @return bool
     */
    protected function is_edusharing_context(array $options) {
        global $COURSE;

        if ( empty($options['context']) ) {
            return false;
        }

        $result = false;
        switch( $options['context']->contextlevel ) {
            case CONTEXT_COURSE:
            case CONTEXT_MODULE:
            case CONTEXT_BLOCK:
                $result = true;
                break;

            default:
                $result = false;
        }

        return $result;
    }

    /**
     * (non-PHPdoc)
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
        if ( $this->is_edusharing_context($options) ) {
            $edusharingticket = $this->mod_edusharing_init_edusharing_ticket();

            // register tinymce-plugin but DO NOT try to load it as this already happened
            $params['plugins'] .= ',-edusharing';

            // add tool-button
            if (empty($params['theme_advanced_buttons3_add'])) {
                $params['theme_advanced_buttons3_add'] = '';
            }
            $params['theme_advanced_buttons3_add'] .= ',|,edusharing';

            // additional params required by edu-sharing.net
            empty($params['extended_valid_elements']) ? $params['extended_valid_elements'] = '' : $params['extended_valid_elements'] .= ',';

            $params['extended_valid_elements'] .= 'a[href|data|type|width|height|alt|title|xmlns::'.self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';
            $params['extended_valid_elements'] .= ',object[data|type|width|height|alt|title|xmlns::'.self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';
            $params['extended_valid_elements'] .= ',img[style|longdesc|usemap|src|border|alt=|title|hspace|vspace|width|height|align|xmlns::'.self::ATTRIBUTE_NAMESPACE_PREFIX.'|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::object_url|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::resource_id|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::mimetype|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_float|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_versionshow|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::window_version|'.self::ATTRIBUTE_NAMESPACE_PREFIX.'::repotype]';

            $params['moodle_wwwroot'] = $CFG->wwwroot;
            $params['edusharing_course_id'] = $COURSE->id;
            $params['edusharing_ticket'] = $edusharingticket;

            $params['edusharing_namespace_uri'] = self::ATTRIBUTE_NAMESPACE_URI;
            $params['edusharing_namespace_prefix'] = self::ATTRIBUTE_NAMESPACE_PREFIX;

            $params['edusharing_dialog_width'] = 550;
            $params['edusharing_dialog_height'] = 400;

            $params['convert_urls'] = false;
        }

        return $params;
    }

    /**
     * Prepare tinymce to use edu-sharing plugin
     *
     * @param string $elementid identifies the id-attribute of editor-node in HTML
     * @param array $options
     * @param array $fpoptions
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
        $PAGE->requires->js_init_code('tinymce.PluginManager.load("edusharing", "' . $CFG->wwwroot . '/lib/editor/edusharing/js/tinymce/plugin/editor_plugin.js?'.filemtime($CFG->libdir . '/editor/edusharing/js/tinymce/plugin/editor_plugin.js').'");');

        return parent::use_editor($elementid, $options, $filepickeroptions);
    }

}
