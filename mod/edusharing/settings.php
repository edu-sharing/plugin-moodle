<?php
// This file is part of edu-sharing created by metaVentis GmbH — http://metaventis.com
//
// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.

/**
 * This file defines the edu-sharing settings
 *
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die ;
global $CFG;

if ($ADMIN->fulltree) {


    if (isset($_POST['section']) && $_POST['section'] == 'modsettingedusharing') {
        $appProperties = json_decode(get_config('edusharing', 'appProperties'), true);
        $repProperties = json_decode(get_config('edusharing', 'repProperties'), true);
        $appPropertiesRequest = $repPropertiesRequest = array();
        
        foreach ($_REQUEST as $key  => $value) {
            if (strpos($key, 'app_') !== false && !empty($value))
                $appProperties[str_replace('app_', '', $key)] = trim($value);
            if (strpos($key, 'rep_') !== false && !empty($value))
                $repProperties[str_replace('rep_', '', $key)] = trim($value);
        }
        
        set_config('appProperties', json_encode($appProperties), 'edusharing');
        set_config('repProperties', json_encode($repProperties), 'edusharing');
        
        set_config('EDU_AUTH_KEY', trim($_REQUEST['EDU_AUTH_KEY']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_USERID', trim($_REQUEST['EDU_AUTH_PARAM_NAME_USERID']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_LASTNAME', trim($_REQUEST['EDU_AUTH_PARAM_NAME_LASTNAME']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', trim($_REQUEST['EDU_AUTH_PARAM_NAME_FIRSTNAME']), 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_EMAIL', trim($_REQUEST['EDU_AUTH_PARAM_NAME_EMAIL']), 'edusharing');
        set_config('EDU_AUTH_AFFILIATION', trim($_REQUEST['EDU_AUTH_AFFILIATION']), 'edusharing');
        set_config('EDU_AUTH_CONVEYGLOBALGROUPS', trim($_REQUEST['EDU_AUTH_CONVEYGLOBALGROUPS']), 'edusharing');
    }
    
    //(re)load config
    $appProperties = json_decode(get_config('edusharing', 'appProperties'), true);
    $repProperties = json_decode(get_config('edusharing', 'repProperties'), true);
    $strSubmit = '<input class="form-submit" type="submit" value="'.get_string('save', 'edusharing').'">';


    $str_txt = get_string('conf_linktext', 'edusharing');
    $str = '<h4 class="main"><a href="' . $CFG->wwwroot . '/mod/edusharing/import_metadata.php?sesskey=' . $USER->sesskey . '" target="_blank">' . $str_txt . '</a></h4>';

    ksort($appProperties);
    $strApp = '';
    foreach ($appProperties as $key  => $value) {
        if (strpos($key, '_key') !== false) {
             $strApp .= '<label for="app_' . $key . '">mod_edusharing/' . $key . '</label>' . '<textarea style="width: 700px" id="app_' . $key . '" name="app_' . $key . '">'.$value.'</textarea><br/>';
        } else {
            $strApp .= '<label for="app_' . $key . '">mod_edusharing/' . $key . '</label>' . '<input style="width: 700px; height: auto;" id="app_' . $key . '" name="app_' . $key . '" type="text" value="' . $value . '"><br/>';
        }
    }
    
    ksort($repProperties);
    $strRep = '';
    foreach ($repProperties as $key  => $value) {
        if (strpos($key, '_key') !== false) {
             $strRep .= '<label for="rep_' . $key . '">mod_edusharing/' . $key . '</label>' . '<textarea style="width: 700px" id="rep_' . $key . '" name="rep_' . $key . '">'.$value.'</textarea><br/>';
        } else {
            $strRep .= '<label for="rep_' . $key . '">mod_edusharing/' . $key . '</label>' . '<input style="width: 700px; height: auto;" id="rep_' . $key . '" name="rep_' . $key . '" type="text" value="' . $value . '"><br/>';
    
        }
    }

    $strAuth = '';
    $strAuth .= '';
    
    $EDU_AUTH_PARAM_NAME_USERID = get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID');
    $EDU_AUTH_KEY = get_config('edusharing', 'EDU_AUTH_KEY');
    $EDU_AUTH_AFFILIATION = get_config('edusharing', 'EDU_AUTH_AFFILIATION');

    $strAuth .= '<label for="EDU_AUTH_KEY">EDU_AUTH_KEY</label><input style="width: 700px; height: auto;" id="EDU_AUTH_KEY" name="EDU_AUTH_KEY" type="text" value="' . get_config('edusharing', 'EDU_AUTH_KEY') . '"><br/>';
    $strAuth .= '<label for="EDU_AUTH_PARAM_NAME_USERID">EDU_AUTH_PARAM_NAME_USERID</label><input style="width: 700px; height: auto;" id="EDU_AUTH_PARAM_NAME_USERID" name="EDU_AUTH_PARAM_NAME_USERID" type="text" value="' . get_config('edusharing', 'EDU_AUTH_PARAM_NAME_USERID') . '"><br/>';
    $strAuth .= '<label for="EDU_AUTH_PARAM_NAME_USERID">EDU_AUTH_PARAM_NAME_LASTNAME</label><input style="width: 700px; height: auto;" id="EDU_AUTH_PARAM_NAME_LASTNAME" name="EDU_AUTH_PARAM_NAME_LASTNAME" type="text" value="' . get_config('edusharing', 'EDU_AUTH_PARAM_NAME_LASTNAME') . '"><br/>';
    $strAuth .= '<label for="EDU_AUTH_PARAM_NAME_FIRSTNAME">EDU_AUTH_PARAM_NAME_FIRSTNAME</label><input style="width: 700px; height: auto;" id="EDU_AUTH_PARAM_NAME_FIRSTNAME" name="EDU_AUTH_PARAM_NAME_FIRSTNAME" type="text" value="' . get_config('edusharing', 'EDU_AUTH_PARAM_NAME_FIRSTNAME') . '"><br/>';
    $strAuth .= '<label for="EDU_AUTH_PARAM_NAME_EMAIL">EDU_AUTH_PARAM_NAME_EMAIL</label><input style="width: 700px; height: auto;" id="EDU_AUTH_PARAM_NAME_EMAIL" name="EDU_AUTH_PARAM_NAME_EMAIL" type="text" value="' . get_config('edusharing', 'EDU_AUTH_PARAM_NAME_EMAIL') . '"><br/>';
    
    $strAuth .= '<br/>';
    
    $strAuth .= '<label for="EDU_AUTH_AFFILIATION">EDU_AUTH_AFFILIATION</label><input style="width: 700px; height: auto;" id="EDU_AUTH_AFFILIATION" name="EDU_AUTH_AFFILIATION" type="text" value="' . get_config('edusharing', 'EDU_AUTH_AFFILIATION') . '"><br/>';
   
    $conveyCohorts = get_config('edusharing', 'EDU_AUTH_CONVEYGLOBALGROUPS');
    $checkNo = $checkYes = '';
    if ($conveyCohorts == 'yes')
        $checkYes = 'checked';
    else
        $checkNo = 'checked';
    $strAuth .= '<label>EDU_AUTH_CONVEYGLOBALGROUPS</label>
                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="cohortsYes" name="EDU_AUTH_CONVEYGLOBALGROUPS" value="yes" ' . $checkYes . '><label for="cohortsYes">&nbsp;' . get_string('convey_global_groups_yes', 'edusharing') . '</label><br>
                     &nbsp;&nbsp;&nbsp;&nbsp;<input type="radio" id="cohortsNo" name="EDU_AUTH_CONVEYGLOBALGROUPS" value="no" ' . $checkNo . '><label for="cohortsNo">&nbsp;' . get_string('convey_global_groups_no', 'edusharing') . '</label>
                 <br/><br/>';
    
    $settings->add(new admin_setting_heading('edusharing', get_string('connectToHomeRepository', 'edusharing'), $str));
    $settings->add(new admin_setting_heading('app', get_string('appProperties', 'edusharing'), $strApp));
    $settings->add(new admin_setting_heading('rep', get_string('homerepProperties', 'edusharing'), $strRep));
    $strAuth .= $strSubmit;
    $settings->add(new admin_setting_heading('auth', get_string('authparameters', 'edusharing'), $strAuth));

}
