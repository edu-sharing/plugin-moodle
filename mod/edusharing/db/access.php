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
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'mod/edusharing:addinstance'  => array(
        'riskbitmask'  => RISK_SPAM,
        'captype'  => 'write',
        'contextlevel'  => CONTEXT_MODULE,
        'archetypes'  => array(
            'editingteacher'  => CAP_ALLOW,
            'manager'  => CAP_ALLOW,
            'admin'  => CAP_ALLOW,
            'teacher'  => CAP_ALLOW
        ),
        'clonepermissionsfrom'  => 'moodle/course:manageactivities'
    ),
);
