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
 * Filter converting edu-sharing URIs in the text to edu-sharing rendering links
 *
 * @package filter_edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot . '/mod/edusharing/lib.php');
require_once($CFG->dirroot . '/mod/edusharing/locallib.php');


/**
 * Parse content for edu-sharing objects to render them
 *
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 */
class filter_edusharing extends moodle_text_filter {

    /**
     * Whether to reset the cache AGAIN or not.
     *
     * @var bool
     */
    private $resettextfiltercache = true;

    /**
     *
     * Enter description here ...
     *
     * @param object $context
     * @param array $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);

        // To force the re-generation of filtered texts we just ...
        // reset_text_filters_cache();
    }

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     *
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
        global $CFG;
        global $COURSE;
        global $PAGE;
        global $edusharing_filter_loaded;
        global $ticket;

        if (!isset($options['originalformat'])) {
            return $text;
        }

        try {

            if (strpos($text, 'edusharing_atto') === false && strpos($text, 'es:resource_id') === false) {
                return $text;
            }

            $context = context_course::instance($COURSE->id);
            // Ensure that user exists in repository.
            if ( isloggedin() && has_capability('moodle/course:view', $context) ) {
                $ccauth = new mod_edusharing_web_service_factory();
                $ticket = $ccauth->edusharing_authentication_get_ticket();
            }else{
                error_log('Cant use edu-sharing filter: Not logged in or not allowed to view course.');
                return $text;
            }

            $memento = $text;

            preg_match_all('#<img(.*)class="(.*)edusharing_atto(.*)"(.*)>#Umsi', $text, $matchesimg_atto,
                    PREG_PATTERN_ORDER);
            preg_match_all('#<a(.*)class="(.*)edusharing_atto(.*)">(.*)</a>#Umsi', $text, $matchesa_atto,
                    PREG_PATTERN_ORDER);
            $matches_atto = array_merge($matchesimg_atto[0], $matchesa_atto[0]);


            preg_match_all('#<img(.*)es:resource_id(.*)>#Umsi', $text, $matchesimg_tinymce,
                PREG_PATTERN_ORDER);
            preg_match_all('#<a(.*)es:resource_id(.*)>(.*)</a>#Umsi', $text, $matchesa_tinymce,
                PREG_PATTERN_ORDER);
            $matches_tinymce = array_merge($matchesimg_tinymce[0], $matchesa_tinymce[0]);

            if (!empty($matches_atto) || !empty($matches_tinymce)) {
                // Disable page-caching to "renew" render-session-data.
                $PAGE->set_cacheable(false);
                if(!$edusharing_filter_loaded) {
                    $PAGE->requires->js_call_amd('filter_edusharing/edu', 'init');
                    $edusharing_filter_loaded = true;
                }

                foreach ($matches_atto as $match) {
                    $text = str_replace($match, $this->filter_edusharing_convert_object($match), $text, $count);
                }

                foreach ($matches_tinymce as $match) {
                    $text = str_replace($match, $this->filter_edusharing_convert_object($match, true), $text, $count);
                }
            }
        } catch (Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
            return $memento;
        }
        return $text;
    }

    /**
     * Prepare object for rendering, wrap rendered object
     *
     * @param string $object
     * @return boolean|string
     */
    private function filter_edusharing_convert_object($object, $tinymce = false) {
        global $DB;
        $doc = new DOMDocument();
        $doc->loadHTML($object);

        if($tinymce) {
            $node = $doc->getElementsByTagName('a')->item(0);
            if (empty($node)) {
                $node = $doc->getElementsByTagName('img')->item(0);
            }
            if (empty($node)) {
                trigger_error(get_string('error_loading_node', 'filter_edusharing'), E_USER_WARNING);
                return false;
            }

            $params = array();
            $params['mimetype'] = $node->getAttribute('es:mimetype');
            $params['mediatype'] = $node->getAttribute('es:mediatype');
            $params['caption'] = $node->getAttribute('es:caption');
            $params['resourceId'] = $node->getAttribute('es:resource_id');



        } else {
            $node = $doc->getElementsByTagName('a')->item(0);
            if (empty($node)) {
                $node = $doc->getElementsByTagName('img')->item(0);
                $qs = $node->getAttribute('src');
            } else {
                $qs = $node->getAttribute('href');
            }
            if (empty($node)) {
                trigger_error(get_string('error_loading_node', 'filter_edusharing'), E_USER_WARNING);
                return false;
            }

            parse_str(parse_url($qs, PHP_URL_QUERY), $params);
        }


        $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => (int) $params['resourceId']));

        if (!$edusharing) {
            trigger_error(get_string('error_loading_resource', 'filter_edusharing'), E_USER_WARNING);
            return false;
        }

        $renderparams = array();
        $height = $node->getAttribute('height');
        $width = $node->getAttribute('width');
        $renderparams['height'] = $height;
        $renderparams['width'] = $width;
        $renderparams['title'] = $node->getAttribute('title');
        $renderparams['mimetype'] = $params['mimetype'];
        $renderparams['mediatype'] = $params['mediatype'];
        $renderparams['caption'] = $params['caption'];
        $converted = $this->filter_edusharing_render_inline($edusharing, $renderparams);
        $wrapperattributes = array();
        $wrapperattributes[] = 'id="' . (int) $params['resourceId'] . '"';
        $wrapperattributes[] = 'class="edu_wrapper"';
        if (strpos($renderparams['mimetype'], 'image') !== false) {
            $wrapperattributes[] = 'data-id="' . (int) $params['resourceId'] . '"';
        }

        $nodestyle = $node->getAttribute('style');
        $styleattr = '';
        switch (true) {
            case (strpos($nodestyle, 'left') > -1):
                $styleattr .= 'display: block; float: left; margin: 0 14px 14px 0;';
                break;
            case (strpos($nodestyle, 'right') > -1):
                $styleattr .= 'display: block; float: right; margin: 0 0 14px 14px;';
                break;
            case ($renderparams['mediatype'] == 'directory' || $renderparams['mediatype'] == 'folder'):
                $styleattr .= 'display: block; margin: 14px 0;';
                break;
            default:
                $styleattr .= 'display: inline-block; margin: 14px 0;';
                break;
        }

        $tagattributes = '';

        if ($width) {
            $styleattr .= ' width: ' . $width . 'px; max-width: 100%;';
            $tagattributes = 'width="' . $width . '"';
        }

        if ($height) {
            $styleattr .= ' height: ' . $height . 'px;';
            $tagattributes = 'height="' . $height . '"';
        }

        $wrapperattributes[] = 'style="' . $styleattr . '"';

        return '<div ' . implode(' ', $wrapperattributes) . ' ' . $tagattributes . '>' . $converted . '</div>';
    }



    /**
     * Build container
     *
     * @param stdClass $edusharing
     * @param array $renderparams
     * @throws Exception
     *
     * @return string
     */
    protected function filter_edusharing_render_inline(stdClass $edusharing, $renderparams) {
        global $CFG, $COURSE, $ticket;

        $objecturl = $edusharing->object_url;
        if (!$objecturl) {
            throw new Exception(get_string('error_empty_object_url', 'filter_edusharing'));
        }
        $url = edusharing_get_redirect_url($edusharing, EDUSHARING_DISPLAY_MODE_INLINE);
        $url .=  '&height=' . urlencode($renderparams['height']) . '&width=' . urlencode($renderparams['width']);

        $inline = '<div class="eduContainer" data-type="esObject" data-url="' . $CFG->wwwroot .
                 '/filter/edusharing/proxy.php?sesskey='.sesskey().'&URL=' . urlencode($url) . '&resId=' .
                 $edusharing->id . '&title=' . urlencode($renderparams['title']) .
                 '&mimetype=' . urlencode($renderparams['mimetype']) .
                 '&mediatype=' . urlencode($renderparams['mediatype']) .
                 '&caption=' . urlencode($renderparams['caption']) .
                 '&course_id=' . urlencode($COURSE -> id) .
                 '&ticket=' . $ticket . '">'.
                 '<div class="edusharing_spinner_inner"><div class="edusharing_spinner1"></div></div>' .
                 '<div class="edusharing_spinner_inner"><div class="edusharing_spinner2"></div></div>'.
                 '<div class="edusharing_spinner_inner"><div class="edusharing_spinner3"></div></div>'.
                 'edu sharing object</div>';
        return $inline;
    }
}
