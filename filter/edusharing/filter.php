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

        // store unfiltered text to return in case of error
        $memento = $text;

        try {
            preg_match_all('#<img(.*)es:resource_id(.*)>#Umsi', $text, $matchesImg, PREG_PATTERN_ORDER);
            preg_match_all('#<a(.*)es:resource_id(.*)>(.*)</a>#Umsi', $text, $matchesA, PREG_PATTERN_ORDER);
            $matches = array_merge($matchesImg[0], $matchesA[0]);
            
            foreach($matches as $match) {
                $text = str_replace($match, $this -> convertObject($match), $text, $count);
            }

        } catch(Exception $exception) {
            trigger_error($exception -> getMessage(), E_USER_WARNING);
            return $memento;
        }

        return $text;
    }

    private function convertObject($object) {
        global $DB;
        $doc = new DOMDocument();
        $doc->loadHTML($object);
        
        $node = $doc->getElementsByTagName('a')[0];
        if(empty($node))
            $node = $doc->getElementsByTagName('img')[0];
        if(empty($node)) {
            trigger_error('Could not get node', E_USER_WARNING);
            return false;
        }

        $edusharing = $DB -> get_record(EDUSHARING_TABLE, array('id' => (int)$node->getAttribute('es:resource_id')));
        if (!$edusharing) {
            trigger_error('Error loading resource from db.', E_USER_WARNING);
            return false;
        }
        $renderParams = array();
        $renderParams['title'] = $node->getAttribute('title');
        $renderParams['mimetype'] = $node->getAttribute('es:mimetype');

        $converted = $this -> filter_edusharing_render_inline($edusharing, $renderParams);
        $wrapperAttributes = array();
        
        $wrapperAttributes[] = 'id="' . (int)$node->getAttribute('es:resource_id') . '"';
        $wrapperAttributes[] = 'class="edu_wrapper"';
        if (strpos($renderParams['mimetype'], 'image') !== false)
            $wrapperAttributes[] = 'data-id="' . (int)$node->getAttribute('es:resource_id') . '"';

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
            $StyleAttr .= ' width: ' . $edusharing->window_width . 'px;';
            $tagAttributes = 'width="' . $edusharing->window_width . '"';
        }

        if ($edusharing -> window_height) {
            $StyleAttr .= ' height: ' . $edusharing->window_height . 'px;';
            $tagAttr[] = 'height="' . $edusharing->window_height . '"';
        }

        $wrapperAttributes[] = 'style="' . $StyleAttr . '"';
        
        return '<div ' . implode(' ', $wrapperAttributes) . ' '. $tagAttributes .'>' . $converted  . '</div>';
    }



    /**
     * Build container
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
        $inline = '<div class="eduContainer" data-type="esObject" data-url="'.$CFG->wwwroot.'/filter/edusharing/proxy.php?URL='.urlencode($url).'&amp;resId='.$edusharing->id.'&amp;title='.urlencode($renderParams['title']).'&amp;mimetype='.$renderParams['mimetype'].'"><div class="inner"><div class="spinner1"></div></div><div class="inner"><div class="spinner2"></div></div><div class="inner"><div class="spinner3"></div></div>edu sharing object</div>';
        
        return $inline;
    }
}
