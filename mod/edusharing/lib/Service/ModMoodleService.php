<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
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

/**
 * Defines a base on which provided edu-sharing services can build upon.
 *
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class ModMoodleService
{

	/**
	 * Re-start given moodle-session. REMEMBER: STOP YOUR CURRENT SESSION BEFORE CALLING THIS METHOD AS IT WILL OVERWRITE IT.
	 *
	 * @param string $sessionId
	 * @return mixed
	 */
	protected function _startMoodleSession($sessionId)
	{

		$currentSessionId = session_id();
		$currentSessionName = session_name();
		session_write_close();

		session_id($sessionId);

		$session = session_get_instance();
		if ( ! $session )
		{
			return false;
		}

		if ( ! $session->session_exists($sessionId) )
		{
			error_log('Session does not exists.');
			return false;
		}

		$currentSessionId = session_id();

		session_write_close();
		session_id($sessionId);
		if ( ! session_start() )
		{
			// cleanup
			session_id($currentSessionId);

			return false;
		}

		return true;
	}

}
