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
 * Definition of log events associated with the current component
 *
 * The log events defined on this file are processed and stored into
 * the Moodle DB after any install or upgrade operation. All plugins
 * support this.
 *
 * For more information, take a look to the documentation available:
 *     - Logging API: {@link http://docs.moodle.org/dev/Logging_API}
 *     - Upgrade API: {@link http://docs.moodle.org/dev/Upgrade_API}
 *
 * @package   mod_edusharing
 * @category  log
 * @copyright 2010 Petr Skoda (http://skodak.org), metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB; // TODO: this is a hack, we should really do something with the SQL in SQL tables

$logs = array(
    array('module' => 'edusharing', 'action' => 'add', 'mtable' => 'edusharing', 'field' => 'name'),
    array('module' => 'edusharing', 'action' => 'update', 'mtable' => 'edusharing', 'field' => 'name'),
    array('module' => 'edusharing', 'action' => 'view', 'mtable' => 'edusharing', 'field' => 'name'),
    array('module' => 'edusharing', 'action' => 'view all', 'mtable' => 'edusharing', 'field' => 'name'),

);
