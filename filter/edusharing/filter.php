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
     * @var array
     */
    protected $appproperties = array();

    /**
     *
     * @var array
     */
    protected $repproperties = array();

    /**
     *
     * Enter description here ...
     *
     * @param object $context
     * @param array $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);

        $this->appproperties = json_decode(get_config('edusharing', 'appProperties'));
        $this->repproperties = json_decode(get_config('edusharing', 'repProperties'));

        // to force the re-generation of filtered texts we just ...
        reset_text_filters_cache();

        // ensure that user exists in repository
        if (isloggedin()) {
            $ccauth = new mod_edusharing_web_service_factory();
            $ccauth->edusharing_authentication_get_ticket($this->appproperties->appid);
        }
    }

    /**
     * Apply the filter to the text
     *
     * @see filter_manager::apply_filter_chain()
     * @param string $text to be processed by the text
     * @param array $options filter options
     * @todo ensure not to include jQuery twice
     *
     * @return string text after processing
     */
    public function filter($text, array $options = array()) {
        global $CFG;
        global $COURSE;
        global $PAGE;

        // disable page-caching to "renew" render-session-data
        $PAGE->set_cacheable(false);

        $PAGE->requires->js('/mod/edusharing/js/jquery.min.js');
        $PAGE->requires->js('/mod/edusharing/js/jquery-near-viewport.min.js');
        $PAGE->requires->js('/filter/edusharing/edu.js');

        if (!isset($options['originalformat'])) {
            return $text;
        }

        // store unfiltered text to return in case of error
        $memento = $text;

        try {
            preg_match_all('#<img(.*)es:resource_id(.*)>#Umsi', $text, $matchesimg,
                    PREG_PATTERN_ORDER);
            preg_match_all('#<a(.*)es:resource_id(.*)>(.*)</a>#Umsi', $text, $matchesa,
                    PREG_PATTERN_ORDER);
            $matches = array_merge($matchesimg[0], $matchesa[0]);

            foreach ($matches as $match) {
                $text = str_replace($match, $this->filter_edusharing_convert_object($match), $text, $count);
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
    private function filter_edusharing_convert_object($object) {
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

        $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => (int) $node->getAttribute('es:resource_id')));

        if (!$edusharing) {
            trigger_error(get_string('error_loading_resource', 'filter_edusharing'), E_USER_WARNING);
            return false;
        }

        $renderparams = array();
        $renderparams['title'] = $node->getAttribute('title');
        $renderparams['mimetype'] = $node->getAttribute('es:mimetype');
        $converted = $this->filter_edusharing_render_inline($edusharing, $renderparams);
        $wrapperattributes = array();

        $wrapperattributes[] = 'id="' . (int) $node->getAttribute('es:resource_id') . '"';
        $wrapperattributes[] = 'class="edu_wrapper"';
        if (strpos($renderparams['mimetype'], 'image') !== false) {
            $wrapperattributes[] = 'data-id="' . (int) $node->getAttribute('es:resource_id') . '"';
        }

        $styleattr = '';
        switch ($edusharing->window_float) {
            case 'left':
                $styleattr .= 'display: block; float: left; margin: 0 5px 5px 0;';
                break;
            case 'right':
                $styleattr .= 'display: block; float: right; margin: 0 0 5px 5px;';
                break;
            case 'inline':
                $styleattr .= 'display: inline-block; margin: 0 5px;';
                break;
            case 'none':
            default:
                $styleattr .= 'display: block; float: none; margin: 5px 0;';
                break;
        }

        if ($edusharing->window_width) {
            $styleattr .= ' width: ' . $edusharing->window_width . 'px;';
            $tagattributes = 'width="' . $edusharing->window_width . '"';
        }

        if ($edusharing->window_height) {
            $styleattr .= ' height: ' . $edusharing->window_height . 'px;';
            $tagattributes = 'height="' . $edusharing->window_height . '"';
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
        global $CFG;

        $objecturl = $edusharing->object_url;
        if (!$objecturl) {
            throw new Exception(get_string('error_empty_object_url', 'filter_edusharing'));
        }

        $repositoryid = $this->appproperties->homerepid;
        $url = edusharing_get_redirect_url($edusharing, $this->appproperties,
                $this->repproperties, EDUSHARING_DISPLAY_MODE_INLINE);
        $inline = '<div class="eduContainer" data-type="esObject" data-url="' . $CFG->wwwroot .
                 '/filter/edusharing/proxy.php?sesskey='.sesskey().'&URL=' . urlencode($url) . '&amp;resId=' .
                 $edusharing->id . '&amp;title=' . urlencode($renderparams['title']) .
                 '&amp;mimetype=' . $renderparams['mimetype'] .
                 '"><div class="inner"><div class="spinner1"></div></div>' .
                 '<div class="inner"><div class="spinner2"></div></div><div class="inner"><div class="spinner3"></div></div>edu sharing object</div>';
        return $inline;
    }
}
