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


function xmldb_filter_edusharing_install() {
    global $CFG;

    // Activate and move the edusharing filter to position 1
    filter_edusharing_reorder();
}

function filter_edusharing_reorder() {

    // The filter enabled is mandatory to be able to display the H5P content.
    filter_set_global_state('edusharing', TEXTFILTER_ON);

    $states = filter_get_global_states();
    $edusharingPos = $states['edusharing']->sortorder;

    while (1 < $edusharingPos) {
        filter_set_global_state('edusharing', TEXTFILTER_ON, -1);
        $edusharingPos--;
    }
}
