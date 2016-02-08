<?php

/**
 * This product Copyright 2013 metaVentis GmbH.  For detailed notice,
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
 * This is a one-line short description of the file
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package     local
 * @subpackage  autoassignrole
 * @author      hippeli
 * @copyright   2013 metaVentis GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/shibbolethDataHandler.php');


function assign($user) {

    global $SESSION;
    
    if($SESSION->rplSsoEntitlement) {

        $shibbo = new ShibboHandler();
        $shibbo -> setUserId($user -> id);
        $shibbo -> setAffiliationsRaw($SESSION->rplSsoEntitlement);
        $shibbo -> assignRoles();
    }
    return true;
}
