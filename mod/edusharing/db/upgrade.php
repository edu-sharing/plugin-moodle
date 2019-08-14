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
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

    if ($result && $oldversion < 2016011401) {

        // usage2
        try {
            $xmldbtable = new xmldb_table('edusharing');
            $sql = 'UPDATE {edusharing} SET object_version = 0 WHERE window_versionshow = 1';
            $DB->execute($sql);
            $sql = 'UPDATE {edusharing} SET object_version = window_version WHERE window_versionshow = 0';
            $DB->execute($sql);
            $xmldbfield = new xmldb_field('window_versionshow');
            $dbman->drop_field($xmldbtable, $xmldbfield);
            $xmldbfield = new xmldb_field('window_version');
            $dbman->drop_field($xmldbtable, $xmldbfield);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

        $homeconf = dirname(__FILE__).'/../conf/esmain/homeApplication.properties.xml';
        if (file_exists($homeconf)) {
            $app = new DOMDocument();
            $app->load($homeconf);
            $app->preserveWhiteSpace = false;
            $entrys = $app->getElementsByTagName('entry');
            foreach ($entrys as $entry) {
                $homeappproperties[$entry->getAttribute('key')] = $entry->nodeValue;
            }

            $homeappproperties['blowfishkey'] = 'thetestkey';
            $homeappproperties['blowfishiv'] = 'initvect';

            set_config('appProperties', json_encode($homeappproperties), 'edusharing');
        }

        $repoconf = dirname(__FILE__).'/../conf/esmain/'.
                    'app-'. $homeappproperties['homerepid'] .'.properties.xml';
        if (file_exists($repoconf)) {
            $app = new DOMDocument();
            $app->load($repoconf);
            $app->preserveWhiteSpace = false;
            $entrys = $app->getElementsByTagName('entry');
            foreach ($entrys as $entry) {
                $repoproperties[$entry->getAttribute('key')] = $entry->nodeValue;
            }

            $repoproperties['authenticationwebservice'] = str_replace('authentication', 'authbyapp', $repoproperties['authenticationwebservice']);
            $repoproperties['authenticationwebservice_wsdl'] = str_replace('authentication', 'authbyapp', $repoproperties['authenticationwebservice_wsdl']);
            if (mb_substr($repoproperties['usagewebservice'], -1) != '2') {
                $repoproperties['usagewebservice'] = $repoproperties['usagewebservice'] . '2';
            }
            $repoproperties['usagewebservice_wsdl'] = str_replace('usage?wsdl', 'usage2?wsdl', $repoproperties['usagewebservice_wsdl']);
            $repoproperties['contenturl'] = $repoproperties['clientprotocol'] . '://' . $repoproperties['domain'] . ':' .
                                            $repoproperties['clientport'] . '/edu-sharing/renderingproxy';

            set_config('repProperties', json_encode($repoproperties), 'edusharing');
        }

        try {

            include(dirname(__FILE__).'/../conf/cs_conf.php');

            set_config('EDU_AUTH_KEY', EDU_AUTH_KEY, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_USERID', EDU_AUTH_PARAM_NAME_USERID, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_LASTNAME', EDU_AUTH_PARAM_NAME_LASTNAME, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', EDU_AUTH_PARAM_NAME_FIRSTNAME, 'edusharing');
            set_config('EDU_AUTH_PARAM_NAME_EMAIL', EDU_AUTH_PARAM_NAME_EMAIL, 'edusharing');

        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
        }

    }

    if ($result && $oldversion < 2016120901) {

        $appproperties = get_config('edusharing', 'appProperties');
        if (!empty($appproperties)) {
            foreach (json_decode($appproperties, true) as $key => $value) {
                set_config('application_' . $key, $value, 'edusharing');
            }
            set_config('appProperties', null, 'edusharing');
        }

        $repproperties = get_config('edusharing', 'repProperties');
        if (!empty($repproperties)) {
            foreach (json_decode($repproperties, true) as $key => $value) {
                set_config('repository_' . $key, $value, 'edusharing');
            }
            set_config('repProperties', null, 'edusharing');
        }
    }

    if ($result && $oldversion < 2018031901) {
        set_config('repository_version', '4.1', 'edusharing');
    }

    if ($result && $oldversion < 2019062110) {

        try {
            $xmldbtable = new xmldb_table('edusharing');
            $xmldbfield = new xmldb_field(
                'module_id',
                XMLDB_TYPE_INTEGER,
                '10',
                null,
                false,
                false,
                null,
                'name'
            );
            $dbman->add_field($xmldbtable, $xmldbfield);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            error_log($e->getMessage());
        }

    }

    if ($result && $oldversion < 2019062401) {

        try {
            $xmldbtable = new xmldb_table('edusharing');
            $xmldbfield = new xmldb_field(
                'section_id',
                XMLDB_TYPE_INTEGER,
                '10',
                null,
                false,
                false,
                null,
                'module_id'
            );
            $dbman->add_field($xmldbtable, $xmldbfield);
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            error_log($e->getMessage());
        }

    }



    return $result;
}
