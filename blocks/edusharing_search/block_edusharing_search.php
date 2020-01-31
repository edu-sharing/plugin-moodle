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
 * Block which allows you to search in the connected edu-sharing-repository.
 *
 * @package    block_edusharing_search
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Block which allows you to search in the connected edu-sharing-repository.
 *
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_edusharing_search extends block_base
{

    /**
     * Initialize this block.
     *
     */
    public function init() {
        $eduIcon = '<svg  version="1.1" id="Layer_1" xmlns="&ns_svg;" xmlns:xlink="&ns_xlink;" width="19.938" height="19.771"
                         viewBox="0 0 19.938 19.771" overflow="visible" enable-background="new 0 0 19.938 19.771" xml:space="preserve">
                        <polygon fill="#3162A7" points="2.748,19.771 0.027,15.06 2.748,10.348 8.188,10.348 10.908,15.06 8.188,19.771 "/>
                        <polygon fill="#7F91C3" points="11.776,14.54 9.056,9.829 11.776,5.117 17.218,5.117 19.938,9.829 17.218,14.54 "/>
                        <polygon fill="#C1C6E3" points="2.721,9.423 0,4.712 2.721,0 8.161,0 10.882,4.712 8.161,9.423 "/>
                    </svg>';
        $this->title   = $eduIcon . ' ' . get_string('block_title', 'block_edusharing_search');
        $this->version = 2015060901;
    }

    /**
     * (non-PHPdoc)
     * @see blocks/block_base::get_content()
     */
    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        global $CFG;
        global $COURSE;

        $this->content         = new stdClass;
        $this->content->text   = '<form action="'.$CFG->wwwroot.'/blocks/edusharing_search/helper/cc_search.php" method="get">
                                 <input type="hidden" name="id" value="'.htmlentities($COURSE->id).'" /><input type="text" style="width: 100%;margin-bottom: 5px;" name="search"
                                 value="'.htmlentities(get_string('search_field_hint', 'block_edusharing_search')).'"
                                 onclick="if (this.value==\''.htmlentities(get_string('search_field_hint', 'block_edusharing_search')).'\') this.value=\'\';" />
                                 <input type="submit" class="btn btn-primary" value="'.htmlentities(get_string('button_text', 'block_edusharing_search')).'" />
                                 <input type="hidden" name="sesskey" value="'.sesskey().'"/></form>';

        return $this->content;
    }

}
