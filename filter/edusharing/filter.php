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

require_once ($CFG -> dirroot . '/mod/edusharing/lib/cclib.php');
require_once ($CFG -> dirroot . '/mod/edusharing/lib.php');
require_once ($CFG -> dirroot . '/mod/edusharing/locallib.php');

class filter_edusharing extends moodle_text_filter {

    /**
     * @var array global configuration for this filter
     *
     * This might be eventually moved into parent class if we found it
     * useful for other filters, too.
     */
    protected static $globalconfig;

    /**
     * Hold repository-tickets as $repository_id => $ticket.
     *
     * @var array
     */
    private $ticketCache = array();

    /**
     * Whether to reset the cache AGAIN or not.
     *
     * @var bool
     */
    private $reset_text_filter_cache = true;

    /**
     * The scripts needed for ajax rendering
     *
     * @var array
     */
    private $scripts = array();
    
    
    protected $appProperties = array();
    protected $repProperties = array();

    /**
     *
     * Enter description here ...
     * @param object $context
     * @param array $localconfig
     */
    public function __construct($context, array $localconfig) {
        parent::__construct($context, $localconfig);
        
        $this -> appProperties = json_decode(get_config('edusharing', 'appProperties'));
        $this -> repProperties = json_decode(get_config('edusharing', 'repProperties'));

        // to force the re-generation of filtered texts we just ...
        reset_text_filters_cache();
        
        //ensure that user exists in repository
        if (isloggedin()){
            $ccauth = new mod_edusharing_web_service_factory();
            $ccauth -> mod_edusharing_authentication_get_ticket($this -> appProperties -> appid);
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
        $PAGE -> set_cacheable(false);

        //@todo ensure not to include jQuery twice
        $PAGE -> requires -> js('/mod/edusharing/js/jquery.min.js');
        $PAGE -> requires -> js('/mod/edusharing/js/jquery-near-viewport.min.js');
        $PAGE -> requires -> js('/filter/edusharing/edu.js');

        if (!isset($options['originalformat'])) {
            // if the format is not specified, we are probably called by {@see format_string()}
            // in that case, it would be dangerous to replace URL with the link because it could
            // be stripped. therefore, we do nothing
            return $text;
        }

        if (in_array($options['originalformat'], explode(',', $this -> get_global_config('formats')))) {
            // $this->convert_urls_into_links($text);
        }

        // store unfiltered text to return in case of error
        $memento = $text;

        try {

            $text = '<?xml version="1.0" encoding="utf-8" ?><div>' . $text . '</div>';

            $DOM = new DOMDocument('1.0');
            $DOM -> formatOutput = true;
            if (!$DOM -> loadXML($text)) {
                throw new Exception('Error loading (X)-HTML to be filtered.');
            }

            $this -> filter_edusharing_traverse($DOM -> documentElement);

            foreach ($this->scripts as $script) {
                $script = new DOMElement('script', $script);
                $DOM -> documentElement -> appendChild($script);
            }

            $this -> scripts = array();

            $text = $DOM -> saveHTML($DOM -> documentElement);
        } catch(Exception $exception) {
            error_log($exception -> getMessage());
            return $memento;
        }

        return $text;
    }

    ////////////////////////////////////////////////////////////////////////////
    // internal implementation starts here
    ////////////////////////////////////////////////////////////////////////////

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
        $this -> load_global_config();
        if (is_null($name)) {
            return self::$globalconfig;

        } elseif (array_key_exists($name, self::$globalconfig)) {
            return self::$globalconfig -> {$name};

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
     * @param DOMNode $RenderNode
     * @param stdClass $edusharing
     */
    protected function filter_edusharing_replace_render_tokens(DOMNode $RenderNode, stdClass $edusharing) {
        $Nodes = array($RenderNode);

        while (!empty($Nodes)) {
            $Node = array_shift($Nodes);
            if ($Node -> hasChildNodes()) {
                foreach ($Node->childNodes as $ChildNode) {
                    $Nodes[] = $ChildNode;
                }
            }

            if ($Node -> nodeType != XML_ELEMENT_NODE) {
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
    protected function filter_edusharing_render_inline(stdClass $edusharing, $renderParams) {
        global $CFG;

        $object_url = $edusharing -> object_url;
        if (!$object_url) {
            throw new Exception('Empty object-url.');
        }

        $repository_id = $this -> appProperties -> homerepid;

        $url = mod_edusharing_get_redirect_url($edusharing, $this -> appProperties, $this -> repProperties, DISPLAY_MODE_INLINE);
        $inline = '<div class="eduContainer" data-type="esObject" data-url="'.$CFG->wwwroot.'/filter/edusharing/proxy.php?URL='.urlencode($url).'&amp;resId='.$edusharing->id.'&amp;title='.urlencode($renderParams['title']).'&amp;mimetype='.$renderParams['mimetype'].'"><div class="inner"><div class="spinner1"></div></div><div class="inner"><div class="spinner2"></div></div><div class="inner"><div class="spinner3"></div></div>edu sahring object</div>';
     

        return $inline;
    }

    /**
     * Filter edu-sharing node.
     *
     * @param string $Node
     */
    protected function filter_edusharing_filter_node(DOMNode $Placeholder) {
        global $CFG;
        global $COURSE;
        global $DB;

        $resource_id = $Placeholder -> getAttribute('es:resource_id');
        if (!$resource_id) {
            //          throw new Exception('Error reading resource-id.');
            return false;
        }

        $edusharing = $DB -> get_record(EDUSHARING_TABLE, array('id' => $resource_id));
        if (!$edusharing) {
            //throw new Exception('Error loading resource from db.');
            //$Placeholder -> parentNode -> removeChild($Placeholder);
            return false;
        }

        $renderParams['title'] = $Placeholder -> getAttribute('title');
        $renderParams['mimetype'] = $Placeholder -> getAttribute('es:mimetype');
        $rendered = $this -> filter_edusharing_render_inline($edusharing, $renderParams);

        if ($rendered) {
            // enforce single-root node for XML comliance
            $rendered = '<div>' . $rendered . '</div>';

            $DOM = new DOMDocument('1.0', 'utf-8');
            if (!$DOM -> loadXML($rendered)) {
                return false;
            }

            $this -> filter_edusharing_replace_render_tokens($DOM -> documentElement, $edusharing);

            $RenderNode = $Placeholder -> ownerDocument -> importNode($DOM -> documentElement, true);
            $RenderNode -> setAttribute('id', 'edu_wrapper_' . $edusharing -> id);

            $RenderNode -> setAttribute('class', 'edu_wrapper');

            if (strpos($renderParams['mimetype'], 'image') !== false)
                $RenderNode -> setAttribute('data-id', $edusharing -> id);

            $Placeholder -> parentNode -> insertBefore($RenderNode, $Placeholder);
            $Placeholder -> parentNode -> removeChild($Placeholder);

            $StyleAttr = '';

            switch($edusharing->window_float) {
                case 'left' :
                    $StyleAttr .= 'display: block; float: left; margin: 0 5px 5px 0;';
                    break;
                case 'right' :
                    $StyleAttr .= 'display: block; float: right; margin: 0 0 5px 5px;';
                    break;
                case 'inline' :
                    $StyleAttr .= 'display: inline-block; margin: 0 5px;';
                    break;
                case 'none' :
                default :
                    $StyleAttr .= 'display: block; float: none; margin: 5px 0;';
                    break;
            }

            if ($edusharing -> window_width) {
                $StyleAttr .= ' width: ' . $edusharing -> window_width . 'px;';
                $RenderNode -> setAttribute('width', $edusharing -> window_width);
            }

            if ($edusharing -> window_height) {
                $StyleAttr .= ' height: ' . $edusharing -> window_height . 'px;';
                $RenderNode -> setAttribute('height', $edusharing -> window_height);
            }

            $RenderNode -> setAttribute('style', $StyleAttr);

            return true;
        }

        return false;
    }

    /**
     *
     * @param DOMNode $Node
     */
    protected function filter_edusharing_traverse(DOMNode $Node) {
        global $CFG;
        global $COURSE;
        global $DB;

        if ($Node -> nodeType == XML_ELEMENT_NODE) {
            // do not use foreach to iterate over DomNodes
            for ($i = 0; $i < $Node -> childNodes -> length; ++$i) {
                $this -> filter_edusharing_traverse($Node -> childNodes -> item($i));
            }

            if ($Node -> hasAttribute('es:resource_id')) {
                if (!$this -> filter_edusharing_filter_node($Node)) {
                    $Node -> parentNode -> removeChild($Node);
                }
            }
        }
    }

}
