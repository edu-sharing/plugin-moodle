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

global $SESSION, $CFG;

require_once (dirname(__FILE__) . '/conf.php');
require_once (dirname(__FILE__) . '/../../config.php');


if($_SERVER['rplSsoEntitlement']) {
    $shibbo = new ShibboHandler();
    $shibbo -> setData($_SERVER);
    $shibbo -> handleLogin();
} else if($_SERVER['givenName']) {
    header('Location: ' . $CFG -> wwwroot);
    die();
}

class ShibboHandler {

    private $data;
    private $uname;
    private $userId = null;
    private $affiliationsRaw;

    public function __construct() {

    }

    public function handleLogin() {

        global $CFG, $SESSION;
        
        // generell Zugriff?
        if ($this -> userHasAccess()) {
            if ($this -> moodleUserExists()) {
                //rollen checken
                $this -> assignRoles();
            } else {
                //set data for eventhandler 'user_created'
                $SESSION->rplSsoEntitlement = $_SERVER['rplSsoEntitlement'];
            }
        } else {
            if ($this -> moodleUserExists()) {
                //rollen löschen
                $this -> deleteRoles();
            } else {
                //redirect to login page
                header('Location: ' . $CFG -> wwwroot);
                die();
            }
        }
    }
    
    private function getUserId() {
        if(!$this->userId)
            $this->userId = get_complete_user_data('username', strtolower($this -> getData('givenName')), $CFG->mnet_localhost_id) -> id;
        return $this -> userId;
    }
    
    public function setUserId($userId) {
        $this -> userId = $userId;
    }
    
    private function getRoleId($role) {
        switch($role) {
            case 'manager':
                return 1;
            break;
            case 'coursecreator':
                return 2;
            break;
            case 'editingteacher':
                return 3;
            break;
            case 'teacher':
                return 4;
            break;
            case 'student':
                return 5;
            break;
            case 'guest':
                return 6;
            break;
            case 'user':
                return 7;
            break;
            case 'frontpage':
            default:
                return 8;
            break;
        }
    }
    
    public function assignRoles() {
        $this->deleteRoles();
        foreach($this->getAffiliations() as $affiliation) {
            if($affiliation[1] == MOODLE_INSTANCE_ID) {
                role_assign($this -> getRoleId($affiliation[0]), $this->getUserId(), get_context_instance(CONTEXT_SYSTEM) -> id);
            } 
        }
    }

    private function deleteRoles() {
        role_unassign_all(array('userid' => $this->getUserId(), 'contextid' => get_context_instance(CONTEXT_SYSTEM) -> id), true, false);
    }
    
    private function userHasAccess() {
        foreach ($this->getAffiliations() as $affiliation) {
            if ($affiliation[1] == MOODLE_INSTANCE_ID)
                return true;
        }
        return false;
    }

    private function moodleUserExists() {
        if (get_complete_user_data('username', strtolower($this -> getData('givenName')), $CFG->mnet_localhost_id))
            return true;
        return false;
    }

    public function setData($data) {
        $this -> data = $data;
    }

    private function getData($key = NULL) {
        if ($key)
            return $this -> data[$key];
        return $this -> data;
    }

    private function getAffiliationsRaw() {
        if(!$this->affiliationsRaw)
            $this->affiliationsRaw = $this -> getData('rplSsoEntitlement');
        return $this->affiliationsRaw;
    }

    private function getAffiliations() {
        $affiliations = array();
        $aa = explode(';', $this -> getAffiliationsRaw());
        foreach ($aa as $a) {
            $affiliations[] = explode('@', $a);
        }
        return $affiliations;
    }
    
    public function setAffiliationsRaw($affiliationsRaw) {
        $this->affiliationsRaw = $affiliationsRaw;
    }

}
