<?php

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot.'/lib/setup.php');

session_get_instance();
require_login();

require_once($CFG->dirroot.'/mod/edusharing/lib.php');

try
{
//	$edusharing = new stdClass();

	$input = file_get_contents('php://input');
	if ( ! $input )
	{
		throw new Exception('Error reading json-data from request-body.');
	}

	$edusharing = json_decode($input);
	if ( ! $edusharing )
	{
		throw new Exception('Error decoding json-data for edusharing-object.');
	}

	$edusharing->intro = '';
	$edusharing->introformat = FORMAT_MOODLE;

	$edusharing = _edusharing_postprocess($edusharing);
	if ( ! $edusharing )
	{
		error_log('Error post-processing resource "'.$edusharing->id.'".');

		header('HTTP/1.1 500 Internal Server Error', true, 500);
		exit();
	}
	$id = edusharing_add_instance($edusharing);
	if ( ! $id )
	{
		throw new Exception('Error adding edu-sharing instance.');
	}

	$edusharing->id = $id;

	$edusharing->src= $CFG->wwwroot . '/lib/editor/edusharing/images/edusharing.png';

	header('Content-type: application/json', true, 200);
	echo json_encode($edusharing);
}
catch(Exception $exception)
{
	error_log( print_r($exception, true) );
	header('HTTP/1.1 500 Internal Server Error', true, 500);
}
