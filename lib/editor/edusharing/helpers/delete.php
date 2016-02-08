<?php

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot.'/lib/setup.php');

session_get_instance();
require_login();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

$input = file_get_contents('php://input');
if ( ! $input )
{
	throw new Exception('Error reading json-data from request-body.');
}

$delete = json_decode($input);
if ( ! $delete )
{
	throw new Exception('Error decoding json-data for edusharing-object.');
}

$where = array(
		'id' => $delete->id,
		'course' => $delete->course,
);
$edusharing = $DB->get_record(EDUSHARING_TABLE, $where);
if ( ! $edusharing )
{
	error_log('Resource "'.$delete->id.'" not found for course "'.$delete->course.'".');

	header('HTTP/1.1 404 Not found', true, 404);
	exit();
}

if ( ! edusharing_delete_instance($edusharing->id) )
{
	error_log('Error deleting edu-sharing-instance "'.$edusharing->id.'"');

	header('', true, 500);
}
