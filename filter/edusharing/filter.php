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
 * Filter converting edu-sharing URIs in the text to edu-sharing rendering links
 *
 * @package    filter
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot . '/mod/edusharing/lib.php');
require_once($CFG->dirroot . '/mod/edusharing/locallib.php');

class filter_edusharing extends moodle_text_filter {

    /**
     * @var array global configuration for this filter
     *
     * This might be eventually moved into parent class if we found it
     * useful for other filters, too.
     */
    protected static $globalconfig;

    /**
     * Hold repository-tickets as $repositoryid  => $ticket.
     *
     * @var array
     */
    private $ticketcache = array();

    /**
     * Whether to reset the cache AGAIN or not.
     *
     * @var bool
     */
    private $resettextfiltercache = true;

    /**
     * The scripts needed for ajax rendering
     *
     * @var array
     */

    protected $appproperties = array();
    protected $repproperties = array();

    /**
     *
     * Enter description here ...
     * @param object $context
     * @param array $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);

        $this->appproperties = json_decode(get_config('edusharing', 'appproperties'));
        $this->repproperties = json_decode(get_config('edusharing', 'repproperties'));

        // to force the re-generation of filtered texts we just ...
        reset_text_filters_cache();

        // ensure that user exists in repository
        if (isloggedin()) {
            $ccauth = new mod_edusharing_web_service_factory();
            $ccauth->mod_edusharing_authentication_get_ticket($this->appproperties->appid);
        }
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

        // disable page-caching to "renew" render-session-data
        $PAGE->set_cacheable(false);

        // @todo ensure not to include jQuery twice
        $PAGE->requires->js('/mod/edusharing/js/jquery.min.js');
        $PAGE->requires->js('/mod/edusharing/js/jquery-near-viewport.min.js');
        $PAGE->requires->js('/filter/edusharing/edu.js');

        if (!isset($options['originalformat'])) {
            // if the format is not specified, we are probably called by {@see format_string()}
            // in that case, it would be dangerous to replace URL with the link because it could
            // be stripped. therefore, we do nothing
            return $text;
        }

        // store unfiltered text to return in case of error
        $memento = $text;

        try {

            $text = '<?xml version="1.0" encoding="utf-8" ?><div>' . $text . '</div>';

            $dom = new DOMDocument('1.0');
            $dom->formatOutput = true;
            if (!$dom->loadHTML($text)) {
                throw new Exception('Error loading (X)-HTML to be filtered.');
            }

            $this->filter_edusharing_traverse($dom->documentElement);

            foreach ($this->scripts as $script) {
                $script = new DOMElement('script', $script);
                $dom->documentElement->appendChild($script);
            }

            $this->scripts = array();

            $text = $dom->saveHTML($dom->documentElement);
        } catch (Exception $exception) {
            trigger_error($exception->getMessage(), E_USER_WARNING);
            return $memento;
        }

        return $text;
    }

    /**
     * Returns the global filter setting
     *
     * If the $name is provided, returns single value. Otherwise returns all
     * global settings in object. Returns null if the named setting is not
     * found.
     *
     * @param mixed $name optional config variable name, defaults to null for all
     * @return string|object|null
     */
    protected function get_global_config($name = null) {
        $this->load_global_config();
        if (is_null($name)) {
            return self::$globalconfig;

        } else if (array_key_exists($name, self::$globalconfig)) {
            return self::$globalconfig->{$name};

        } else {
            return null;
        }
    }

    /**
     * Makes sure that the global config is loaded in $this->globalconfig
     *
     * @return void
     */
    protected function load_global_config() {
        if (is_null(self::$globalconfig)) {
            self::$globalconfig = get_config('filter_edusharing');
        }
    }

    /**
     * Replaces tokens inserted by renderservice to be replaced on "client"-side
     *
     * @param DOMNode $rendernode
     * @param stdClass $edusharing
     */
    protected function filter_edusharing_replace_render_tokens(DOMNode $rendernode, stdClass $edusharing) {
        $nodes = array($rendernode);

        while (!empty($nodes)) {
            $node = array_shift($nodes);
            if ($node->hasChildNodes()) {
                foreach ($node->childNodes as $childnode) {
                    $nodes[] = $childnode;
                }
            }

            if ($node->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

        }
    }

    /**
     * Request inline-rendered snippet from repository's render-service.
     *
     * @param stdClass $edusharing
     * @throws Exception
     *
     * @return string
     */
    protected function filter_edusharing_render_inline(stdClass $edusharing, $renderparams) {
        global $CFG;

        $objecturl = $edusharing->object_url;
        if (!$objecturl) {
            throw new Exception('Empty object-url.');
        }

        $repositoryid = $this->appproperties->homerepid;

        $url = mod_edusharing_get_redirect_url($edusharing, $this->appproperties, $this->repproperties, DISPLAY_MODE_INLINE);
        $inline = '<div class="eduContainer" data-type="esObject" data-url="'.$CFG->wwwroot.'/filter/edusharing/proxy.php?URL='.urlencode($url).'&amp;resId='.$edusharing->id.'&amp;title='.urlencode($renderparams['title']).'&amp;mimetype='.$renderparams['mimetype'].'"><div class="inner"><div class="spinner1"></div></div><div class="inner"><div class="spinner2"></div></div><div class="inner"><div class="spinner3"></div></div>edu sharing object</div>';

        return $inline;
    }

    /**
     * Filter edu-sharing node.
     *
     * @param string $node
     */
    protected function filter_edusharing_filter_node(DOMNode $placeholder) {
        global $CFG;
        global $COURSE;
        global $DB;

        $resourceid = $placeholder->getAttribute('es:resource_id');
        if (!$resourceid) {
            trigger_error('Error reading resource-id.', E_USER_WARNING);
            return false;
        }

        $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id'  => $resourceid));
        if (!$edusharing) {
            trigger_error('Error loading resource from db.', E_USER_WARNING);
            return false;
        }

        $renderparams['title'] = $placeholder->getAttribute('title');
        $renderparams['mimetype'] = $placeholder->getAttribute('es:mimetype');
        $rendered = $this->filter_edusharing_render_inline($edusharing, $renderparams);

        if ($rendered) {
            // enforce single-root node for XML comliance
            $rendered = '<div>' . $rendered . '</div>';

            $dom = new DOMDocument('1.0', 'utf-8');
            if (!$dom->loadXML($rendered)) {
                return false;
            }

            $this->filter_edusharing_replace_render_tokens($dom->documentElement, $edusharing);

            $rendernode = $placeholder->ownerDocument->importNode($dom->documentElement, true);
            $rendernode->setAttribute('id', 'edu_wrapper_' . $edusharing->id);

            $rendernode->setAttribute('class', 'edu_wrapper');

            if (strpos($renderparams['mimetype'], 'image') !== false)
                $rendernode->setAttribute('data-id', $edusharing->id);

            $placeholder->parentNode->insertBefore($rendernode, $placeholder);
            $placeholder->parentNode->removeChild($placeholder);

            $styleattr = '';

            switch($edusharing->window_float) {
                case 'left' :
                    $styleattr .= 'display: block; float: left; margin: 0 5px 5px 0;';
                    break;
                case 'right' :
                    $styleattr .= 'display: block; float: right; margin: 0 0 5px 5px;';
                    break;
                case 'inline' :
                    $styleattr .= 'display: inline-block; margin: 0 5px;';
                    break;
                case 'none' :
                default :
                    $styleattr .= 'display: block; float: none; margin: 5px 0;';
                    break;
            }

            if ($edusharing->window_width) {
                $styleattr .= ' width: ' . $edusharing->window_width . 'px;';
                $rendernode->setAttribute('width', $edusharing->window_width);
            }

            if ($edusharing->window_height) {
                $styleattr .= ' height: ' . $edusharing->window_height . 'px;';
                $rendernode->setAttribute('height', $edusharing->window_height);
            }

            $rendernode->setAttribute('style', $styleattr);

            return true;
        }

        return false;
    }

    /**
     *
     * @param DOMNode $node
     */
    protected function filter_edusharing_traverse(DOMNode $node) {
        global $CFG;
        global $COURSE;
        global $DB;

        if ($node->nodeType == XML_ELEMENT_NODE) {
            // do not use foreach to iterate over DomNodes
            for ($i = 0; $i < $node->childNodes->length; ++$i) {
                $this->filter_edusharing_traverse($node->childNodes->item($i));
            }

            if ($node->hasAttribute('es:resource_id')) {
                if (!$this->filter_edusharing_filter_node($node)) {
                    $node->parentNode->removeChild($node);
                }
            }
        }
    }

}
