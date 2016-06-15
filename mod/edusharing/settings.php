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
 * This file defines the edu-sharing settings
 *
 * @package mod
 * @subpackage edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;

if ($ADMIN->fulltree) {

    if (isset($_POST['section']) && $_POST['section'] == 'modsettingedusharing') {
        $appproperties = json_decode(get_config('edusharing', 'appProperties'), true);
        $repproperties = json_decode(get_config('edusharing', 'repProperties'), true);

        foreach ($_REQUEST as $key => $value) {
            if (strpos($key, 'app_') !== false && !empty($value)) {
                $appproperties[str_replace('app_', '', $key)] = trim($value);
            }
            if (strpos($key, 'rep_') !== false && !empty($value)) {
                $repproperties[str_replace('rep_', '', $key)] = trim($value);
            }
        }

        set_config('appProperties', json_encode($appproperties), 'edusharing');
        set_config('repProperties', json_encode($repproperties), 'edusharing');

        set_config('EDU_AUTH_KEY', trim($_REQUEST['EDU_AUTH_KEY']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_USERID', trim($_REQUEST['EDU_AUTH_PARAM_NAME_USERID']),
                'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_LASTNAME', trim($_REQUEST['EDU_AUTH_PARAM_NAME_LASTNAME']),
                'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME',
                trim($_REQUEST['EDU_AUTH_PARAM_NAME_FIRSTNAME']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_EMAIL', trim($_REQUEST['EDU_AUTH_PARAM_NAME_EMAIL']),
                'edusharing');
        set_config('EDU_AUTH_AFFILIATION', trim($_REQUEST['EDU_AUTH_AFFILIATION']), 'edusharing');
        set_config('EDU_AUTH_CONVEYGLOBALGROUPS', trim($_REQUEST['EDU_AUTH_CONVEYGLOBALGROUPS']),
                'edusharing');
    }

    // (re)load config
    $appproperties = json_decode(get_config('edusharing', 'appProperties'), true);
    $repproperties = json_decode(get_config('edusharing', 'repProperties'), true);
    $strsubmit = '<input class="form-submit" type="submit" value="' .
             get_string('save', 'edusharing') . '">';

    $strtxt = get_string('conf_linktext', 'edusharing');
    $str = '<h4 class="main"><a href="' . $CFG->wwwroot .
             '/mod/edusharing/import_metadata.php?sesskey=' . $USER->sesskey . '" target="_blank">' .
             $strtxt . '</a></h4>';

    $strapp = '';
    if (!empty($appproperties)) {
        ksort($appproperties);
        foreach ($appproperties as $key => $value) {
            if (strpos($key, '_key') !== false) {
                $strapp .= '<label for="app_' . $key . '">mod_edusharing/' . $key . '</label>' .
                         '<textarea style="width: 700px" id="app_' . $key . '" name="app_' . $key . '">' .
                         $value . '</textarea><br/>';
            } else {
                $strapp .= '<label for="app_' . $key . '">mod_edusharing/' . $key . '</label>' .
                         '<input style="width: 700px; height: auto;" id="app_' . $key . '" name="app_' .
                         $key . '" type="text" value="' . $value . '"><br/>';
            }
        }
    }

    $strrep = '';
    if (!empty($repproperties)) {
        ksort($repproperties);
        foreach ($repproperties as $key => $value) {
            if (strpos($key, '_key') !== false) {
                $strrep .= '<label for="rep_' . $key . '">mod_edusharing/' . $key . '</label>' .
                         '<textarea style="width: 700px" id="rep_' . $key . '" name="rep_' . $key . '">' .
                         $value . '</textarea><br/>';
            } else {
                $strrep .= '<label for="rep_' . $key . '">mod_edusharing/' . $key . '</label>' .
                         '<input style="width: 700px; height: auto;" id="rep_' . $key . '" name="rep_' .
                         $key . '" type="text" value="' . $value . '"><br/>';
            }
        }
    }

    $strauth = '';
    $strauth .= '';

    $eduauthparamnameuserid = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
    $eduauthkey = get_config('edusharing', 'EDU_AUTH_KEY');
    $eduauthaffiliation = get_config('edusharing', 'EDU_AUTH_AFFILIATION');

    $strauth .= '<label for="EDU_AUTH_KEY">EDU_AUTH_KEY</label><input style="width: 700px; height: auto;" id="EDU_AUTH_KEY" name="EDU_AUTH_KEY" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_KEY') . '"><br/>';
    $strauth .= '<label for="EDU_AUTH_PARAM_NAME_USERID">EDU_AUTH_PARAM_NAME_USERID</label><input style="width: 700px; height: auto;" '.
            'id="EDU_AUTH_PARAM_NAME_USERID" name="EDU_AUTH_PARAM_NAME_USERID" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID') . '"><br/>';
    $strauth .= '<label for="EDU_AUTH_PARAM_NAME_USERID">EDU_AUTH_PARAM_NAME_LASTNAME</label><input style="width: 700px; height: auto;" '.
            'id="EDU_AUTH_PARAM_NAME_LASTNAME" name="EDU_AUTH_PARAM_NAME_LASTNAME" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_PARAM_NAME_LASTNAME') . '"><br/>';
    $strauth .= '<label for="EDU_AUTH_PARAM_NAME_FIRSTNAME">EDU_AUTH_PARAM_NAME_FIRSTNAME</label><input style="width: 700px; height: auto;" '.
            'id="EDU_AUTH_PARAM_NAME_FIRSTNAME" name="EDU_AUTH_PARAM_NAME_FIRSTNAME" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_PARAM_NAME_FIRSTNAME') . '"><br/>';
    $strauth .= '<label for="EDU_AUTH_PARAM_NAME_EMAIL">EDU_AUTH_PARAM_NAME_EMAIL</label><input style="width: 700px; height: auto;" '.
            'id="EDU_AUTH_PARAM_NAME_EMAIL" name="EDU_AUTH_PARAM_NAME_EMAIL" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_PARAM_NAME_EMAIL') . '"><br/>';

    $strauth .= '<br/>';

    $strauth .= '<label for="EDU_AUTH_AFFILIATION">EDU_AUTH_AFFILIATION</label><input style="width: 700px; height: auto;" id="EDU_AUTH_AFFILIATION" '.
            'name="EDU_AUTH_AFFILIATION" type="text" value="' .
             get_config('edusharing', 'EDU_AUTH_AFFILIATION') . '"><br/>';

    $conveycohorts = get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS');
    $checkno = $checkyes = '';
    if ($conveycohorts == 'yes') {
        $checkyes = 'checked';
    } else {
        $checkno = 'checked';
    }
    $strauth .= '<label>EDU_AUTH_CONVEYGLOBALGROUPS</label>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="cohortsYes" name="EDU_AUTH_CONVEYGLOBALGROUPS" value="yes" ' .
             $checkyes . '><label for="cohortsYes">&nbsp;' .
             get_string('convey_global_groups_yes', 'edusharing') .
             '</label><br>&nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="cohortsNo" name="EDU_AUTH_CONVEYGLOBALGROUPS" value="no" ' .
             $checkno . '><label for="cohortsNo">&nbsp;' .
             get_string('convey_global_groups_no', 'edusharing') . '</label><br/><br/>';

    $settings->add(
            new admin_setting_heading('edusharing',
                    get_string('connectToHomeRepository', 'edusharing'), $str));
    $settings->add(
            new admin_setting_heading('app', get_string('appProperties', 'edusharing'), $strapp));
    $settings->add(
            new admin_setting_heading('rep', get_string('homerepProperties', 'edusharing'), $strrep));
    $strauth .= $strsubmit;
    $settings->add(
            new admin_setting_heading('auth', get_string('authparameters', 'edusharing'), $strauth));
}
