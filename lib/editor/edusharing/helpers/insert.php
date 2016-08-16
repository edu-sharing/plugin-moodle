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
 * Called on object insertion
 *
 * @package    editor_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot.'/lib/setup.php');

require_login();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

try {

    $input = file_get_contents('php://input');
    if ( ! $input ) {
        throw new Exception('Error reading json-data from request-body.');
    }

    $edusharing = json_decode($input);
    if ( ! $edusharing ) {
        throw new Exception('Error decoding json-data for edusharing-object.');
    }

    $edusharing->intro = '';
    $edusharing->introformat = FORMAT_MOODLE;

    $edusharing = mod_edusharing_postprocess($edusharing);
    if ( ! $edusharing ) {
        trigger_error('Error post-processing resource "'.$edusharing->id.'".', E_USER_WARNING);

        header('HTTP/1.1 500 Internal Server Error', true, 500);
        exit();
    }
    $id = edusharing_add_instance($edusharing);
    if ( ! $id ) {
        throw new Exception('Error adding edu-sharing instance.');
    }

    $edusharing->id = $id;

    $edusharing->src = $CFG->wwwroot . '/lib/editor/edusharing/images/edusharing.png';

    header('Content-type: application/json', true, 200);
    echo json_encode($edusharing);
} catch (Exception $exception) {
    trigger_error($exception->getMessage(), E_USER_WARNING);
    header('HTTP/1.1 500 Internal Server Error', true, 500);
}
