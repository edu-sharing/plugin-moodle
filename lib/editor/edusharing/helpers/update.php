<?php

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once(dirname(__FILE__) . '/../../../../lib/setup.php');

session_get_instance();
require_login();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

$input = file_get_contents('php://input');
if ( ! $input )
{
	throw new Exception('Error reading json-data from request-body.');
}

$update = json_decode($input);
if ( ! $update )
{
	throw new Exception('Error parsing json-text.');
}

$where = array(
	'id' => $update->id,
	'course' => $update->course,
);
$edusharing = $DB->get_record(EDUSHARING_TABLE, $where);
if ( ! $edusharing )
{
	error_log('Resource "'.$update->id.'" not found for course "'.$update->course.'".');

	header('HTTP/1.1 404 Not found', true, 404);
	exit();
}

// set $instance to $id to conform with moodle's resource-handling
// $edusharing->instance = $edusharing->id;

// post-process given data
$edusharing = _edusharing_postprocess($update);
if ( ! $edusharing )
{
	error_log('Error post-processing resource "'.$edusharing->id.'".');

	header('HTTP/1.1 500 Internal Server Error', true, 500);
	exit();
}

if ( ! edusharing_update_instance($edusharing) )
{
	error_log('Error updating resource "'.$edusharing->id.'".');

	header('HTTP/1.1 500 Internal Server Error', true, 500);
	exit();
}

header('Content-Type: application/json', true, 200);
echo json_encode($edusharing);
