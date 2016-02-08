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
 * This file keeps track of upgrades to the edusharing module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installtion to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in
 * lib/ddllib.php
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_edusharing_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_edusharing_upgrade($oldversion=0) {

    global $CFG, $THEME, $DB;

    $dbman = $DB->get_manager(); // loads ddl manager and xmldb classes

    $result = true;

/// And upgrade begins here. For each one, you'll need one
/// block of code similar to the next one. Please, delete
/// this comment lines once this file start handling proper
/// upgrade code.

/// if ($result && $oldversion < YYYYMMDD00) { //New version in version.php
///     $result = result of "/lib/ddllib.php" function calls
/// }

    /*
     * edu-sharing instances come in 2 flavors: inline and course-module. As
     * inline-objects do not carry a course-id and course-modules are assigned
     * their course-id by moodle itself, we can drop this column now.
     */

    if ($result && $oldversion < 2012051504)
    {
/*
    	$table = new xmldb_table('edusharing');

    	$index = new xmldb_index('course');
    	$result = $dbman->drop_index($table, $index);

    	$column = new xmldb_field('course');
        $result = $dbman->drop_field($table, $column);
*/
    }
    
    if ($result && $oldversion < 2013072301)
    {

        $xmldb_table = new xmldb_table('edusharing');

        $xmldb_field = new xmldb_field('window_float', XMLDB_TYPE_CHAR, 20, null, true, false, 'none');
        $dbman->add_field($xmldb_table, $xmldb_field);
        
        $xmldb_field = new xmldb_field('window_versionshow', XMLDB_TYPE_CHAR, 20, null, true, false, 'latest');
        $dbman->add_field($xmldb_table, $xmldb_field);
        
        $xmldb_field = new xmldb_field('window_version', XMLDB_TYPE_CHAR, 20, null, false, false);
        $dbman->add_field($xmldb_table, $xmldb_field);
        
    }
    
    /*
     * 
     * usage2 will come
     * 
    if ($result && $oldversion < 2015010800) {

        $xmldb_table = new xmldb_table('edusharing');
        
        $sql = 'UPDATE {edusharing} SET object_version = 0 WHERE window_versionshow = 1';
        $DB->execute($sql);
        
        $sql = 'UPDATE {edusharing} SET object_version = window_version WHERE window_versionshow = 0';
        $DB->execute($sql);

        $xmldb_field = new xmldb_field('window_versionshow');
        $dbman ->drop_field($xmldb_table, $xmldb_field);
        
        $xmldb_field = new xmldb_field('window_version');
        $dbman ->drop_field($xmldb_table, $xmldb_field);
        
        
    }
     * */

/// And that's all. Please, examine and understand the 3 example blocks above. Also
/// it's interesting to look how other modules are using this script. Remember that
/// the basic idea is to have "blocks" of code (each one being executed only once,
/// when the module version (version.php) is updated.

/// Lines above (this included) MUST BE DELETED once you get the first version of
/// yout module working. Each time you need to modify something in the module (DB
/// related, you'll raise the version and add one upgrade block here.

/// Final return of upgrade result (true/false) to Moodle. Must be
/// always the last line in the script
    return $result;
}
