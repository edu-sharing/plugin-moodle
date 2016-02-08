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
 * @subpackage  destroyessession
 * @author      hippeli
 * @copyright   2014 metaVentis GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();


function destroy() {
    global $USER, $CFG;

    try {
        require_once ('../config.php');
        require_once ('../mod/edusharing/lib/ESApp.php');
        require_once ('../mod/edusharing/lib/EsApplication.php');
        require_once ('../mod/edusharing/lib/EsApplications.php');
        require_once ('../mod/edusharing/conf/cs_conf.php');
        require_once ('../mod/edusharing/lib/cclib.php');
        require_once ('../mod/edusharing/lib.php');
        $es = new ESApp();
        $app = $es -> getApp(EDUSHARING_BASENAME);
        $conf = $es -> getHomeConf();
        $propArray = $conf -> prop_array;   
        if(!empty($USER -> ticket)) {
            $serviceUrl = str_replace('services', '', $propArray['cc_webservice_url']) . 'logout?ticket=' . $USER -> ticket;
            $curly = curl_init();
            curl_setopt($curly, CURLOPT_URL, $serviceUrl);
            $res = curl_exec($curly);
            curl_close($curly);
        }
    } catch(Exception $e) {}
}
