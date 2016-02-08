<?php
/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

$wsdl = dirname(__FILE__) . '/permission.wsdl';
if ( ! file_exists($wsdl) )
{
	$wsdl = dirname(__FILE__).'/permissionservice.wsdl';
}

if ( ! file_exists($wsdl) )
{
	header('HTTP/1.1 500 Internal Server Error');
	trigger_error('WSDL "'.$wsdl.'" not found.');
}

// deliver wsdl if requested
if ( isset($_GET['wsdl']) )
{
	echo file_get_contents($wsdl, false);
	exit();
}

// $nomoodlecookie = true;

require_once ('../lib/Service/ModMoodleService.php');

require_once ('../conf/cs_conf.php');
require_once ('../../../config.php');


/**
 * Class provides the required permission-service for moodle to take part in a
 * edu-sharing network.
 */
class CCPermissionService
extends ModMoodleService
{

	/**
	 * The faculty member-role.
	 * @var string
	 */
	const ROLE_FACULTY = 'faculty';

	/**
	 * The students role.
	 * @var string
	 */
	const ROLE_STUDENT = 'student';

	/**
	 * The university-staff role.
	 * @var string
	 */
	const ROLE_STAFF = 'staff';

	/**
	 * The employee role.
	 * @var string
	 */
	const ROLE_EMPLOYEE = 'employee';

	/**
	 * An alumni of this institution.
	 * @var string
	 */
	const ROLE_ALUM = 'alum';

	/**
	 * A unspecified member of this institution.
	 * @var string
	 */
	const ROLE_MEMBER = 'member';

	/**
	 * Some somehow affiliated person.
	 * @var string
	 */
	const ROLE_AFFILIATE = 'affiliate';

	/**
	 * Implements PermissionService::getPermission().
	 *
	 * @param stdClass $getPermissionRequest
	 *
	 * @return array
	 */
	public function getPermission($getPermissionRequest)
	{
		error_log('PermissionService::getPermission() called');

		if ( empty($getPermissionRequest->session) )
		{
			error_log('PermissionService::getPermission(): Missing param "session".');
			return array("getPermissionReturn" => false);
		}
		$reqSession = $getPermissionRequest->session; // session of requesting moodle user

		if ( empty($getPermissionRequest->courseid) )
		{
			error_log('PermissionService::getPermission(): Missing param "courseid".');
			return array("getPermissionReturn" => false);
		}
		$reqCourse = $getPermissionRequest->courseid; // actual course where moodle user is inside

		if ( empty($getPermissionRequest->action) )
		{
			error_log('PermissionService::getPermission(): Missing param "action".');
			return array("getPermissionReturn" => false);
		}
		$reqCapability = $getPermissionRequest->action; // requested capability e.g. 'moodle/course:update' for a list of capab. look at: http://docs.moodle.org/en/index.php?title=Category:Capabilities

		// Stimmt die Session die übergeben wird?
		if ( ! $this->_startMoodleSession($getPermissionRequest->session) ) {
			error_log('PermissionService::getPermission(): Error reading session-data.');
			return array("getPermissionReturn" => false);
		}

		// is there a current user in session?
		if ( empty($_SESSION['USER']->id) ) {
			error_log('PermissionService::getPermission(): Session contains no current user-id'.$getPermissionRequest->session.'".');
			return array("getPermissionReturn" => false);
		}

		$currentUserId = $_SESSION['USER']->id;

		//.oO load context
		$CONTEXT_COURSE = 50;
		$context = get_context_instance($CONTEXT_COURSE, $reqCourse);
		if ( ! $context )
		{
			error_log('PermissionService::getPermission(): Required context not found."'.$getPermissionRequest->session.'".');
			return array("getPermissionReturn" => false);
		}

		//.oO check if user has capability in context
		$hasCapability = has_capability($reqCapability, $context, $currentUserId);
		if ( $hasCapability )
		{
			error_log('PermissionService::getPermission(): Granting permission "'.$reqCapability.'" for user-id "'.$currentUserId.'" in context "'.$CONTEXT_COURSE.'".');
			return array("getPermissionReturn" => true);
		}

		error_log('PermissionService::getPermission(): Denying permission "'.$reqCapability.'"');
		return array("getPermissionReturn" => false);
	}

	/**
	 * Implements PermissionService::getPrimaryRole().
	 *
	 * @param stdClass $getPrimaryRoleRequest
	 *
	 * @return array
	 */
	public function getPrimaryRole($getPrimaryRoleRequest)
	{
		// as moodle does not assign a primary role to an user, we'll use the least surprising option here
		return array('getPrimaryRoleOut' => self::ROLE_STUDENT);
	}

	public function checkCourse($checkCourseRequest)
	{
		throw new SoapFault('Not implemented yet. Possibly deprecated.');
	}

}

libxml_disable_entity_loader(false);
$server = new SoapServer($wsdl);
$server->setClass("CCPermissionService");
$server->handle();
