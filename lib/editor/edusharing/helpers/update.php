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
 * @package    editor
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once(dirname(__FILE__) . '/../../../../lib/setup.php');

require_login();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

$input = file_get_contents('php://input');
if ( ! $input ) {
    throw new Exception('Error reading json-data from request-body.');
}

$update = json_decode($input);
if ( ! $update ) {
    throw new Exception('Error parsing json-text.');
}

$where = array(
    'id'  => $update->id,
    'course'  => $update->course,
);
$edusharing = $DB->get_record(EDUSHARING_TABLE, $where);
if ( ! $edusharing ) {
    trigger_error('Resource "'.$update->id.'" not found for course "'.$update->course.'".', E_USER_WARNING);

    header('HTTP/1.1 404 Not found', true, 404);
    exit();
}

// set $instance to $id to conform with moodle's resource-handling
// $edusharing->instance = $edusharing->id;

// post-process given data
$edusharing = mod_edusharing_postprocess($update);
if ( ! $edusharing ) {
    trigger_error('Error post-processing resource "'.$edusharing->id.'".', E_USER_WARNING);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    exit();
}

if ( ! edusharing_update_instance($edusharing) ) {
    trigger_error('Error updating resource "'.$edusharing->id.'".', E_USER_WARNING);

    header('HTTP/1.1 500 Internal Server Error', true, 500);
    exit();
}

header('Content-Type: application/json', true, 200);
echo json_encode($edusharing);
