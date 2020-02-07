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
 * @package mod_edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $hint = '<div class="form-defaultinfo small text-muted " style="margin-top: 6px">'.get_string('conf_hinttext', 'edusharing').'</div>';
    $hint = '';
    $str = '<div class="form-item row">
                <div class="form-label col-sm-3 text-sm-right">
                    <p>'.get_string('conf_linktext', 'edusharing').'</p>
                    
                </div>
                <div class="form-setting col-sm-9">
                    <div class="form-text defaultsnext">
                        <a class="btn btn-primary" style="margin-top: 5px;" href="' . $CFG->wwwroot .
                        '/mod/edusharing/import_metadata.php?sesskey=' . $USER->sesskey . '" target="_blank">'.
                        get_string('conf_btntext', 'edusharing').'</a>
                    </div>'.$hint.'
                </div>
        </div>';

    $settings->add(
            new admin_setting_heading('edusharing',
                get_string('connectToHomeRepository', 'edusharing'), $str));


    $settings->add(new admin_setting_heading('edusharing/app', get_string('appProperties', 'edusharing'), ''));

    $settings->add(new admin_setting_configtext('edusharing/application_appid', 'appid', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/application_type', 'type', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/application_homerepid', 'homerepid', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/application_cc_gui_url', 'cc_gui_url', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtextarea('edusharing/application_private_key', 'private_key', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtextarea('edusharing/application_public_key', 'public_key', '', '', PARAM_TEXT, 50));

    if(version_compare(get_config('edusharing', 'repository_version'), '4.1' ) < 0) {

        $settings->add(new admin_setting_configtext('edusharing/application_blowfishkey', 'blowfishkey', '', '', PARAM_TEXT, 50));

        $settings->add(new admin_setting_configtext('edusharing/application_blowfishiv', 'blowfishiv', '', '', PARAM_TEXT, 50));

    }

    $settings->add(new admin_setting_heading('edusharing/rep', get_string('homerepProperties', 'edusharing'), ''));

    $settings->add(new admin_setting_configtextarea('edusharing/repository_public_key', 'public_key', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_clientport', 'clientport', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_port', 'port', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_domain', 'domain', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_authenticationwebservice_wsdl',
            'authenticationwebservice_wsdl', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_type', 'type', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_appid', 'appid', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_usagewebservice_wsdl',
            'usagewebservice_wsdl', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_protocol', 'protocol', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_host', 'host', '', '', PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/repository_version', 'version', '', '4.1', PARAM_TEXT, 50));



    $settings->add(new admin_setting_heading('edusharing/auth', get_string('authparameters', 'edusharing'), ''));

    // Defaults according to locallib.php.
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_KEY', 'EDU_AUTH_KEY',
            '', 'username', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_PARAM_NAME_USERID',
            'PARAM_NAME_USERID', '', 'userid', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_PARAM_NAME_LASTNAME',
            'PARAM_NAME_LASTNAME', '', 'lastname', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_PARAM_NAME_FIRSTNAME',
            'PARAM_NAME_FIRSTNAME', '', 'firstname', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_PARAM_NAME_EMAIL',
            'PARAM_NAME_EMAIL', '', 'email', PARAM_TEXT, 50));
    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_AFFILIATION',
            'AFFILIATION', '', $CFG->siteidentifier, PARAM_TEXT, 50));

    $settings->add(new admin_setting_configtext('edusharing/EDU_AUTH_AFFILIATION_NAME',
        'AFFILIATION_NAME', '', $CFG->siteidentifier, PARAM_TEXT, 50));

    $settings->add(new admin_setting_configcheckbox('edusharing/EDU_AUTH_CONVEYGLOBALGROUPS', 'CONVEYGLOBALGROUPS', '', ''));

    $settings->add(new admin_setting_heading('edusharing/guest', get_string('guestProperties', 'edusharing'), ''));

    $settings->add(new admin_setting_configcheckbox('edusharing/edu_guest_option', 'guest_option', '', ''));

    $settings->add(new admin_setting_configtext('edusharing/edu_guest_guest_id', 'guest_id', '', 'esguest', PARAM_TEXT, 50));


    $settings->add(new admin_setting_heading('edusharing/save', get_string('save', 'edusharing'), ''));


}
