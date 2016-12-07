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
 * Get repository properties and generate app properties - put them to configuration
 *
 * @package mod_edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
?>
<html>
<head>
<title>edu-sharing metadata import</title>
<style type="text/css" id="vbulletin_css">
body {
    background: #e4f3f9;
    color: #000000;
    font: 11pt verdana, geneva, lucida, 'lucida grande', arial, helvetica,
    sans-serif;
    margin: 5px 10px 10px 10px;
    padding: 0px;
}

table {
    background: #e4f3f9;
    color: #000000;
    font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica,
    sans-serif;
    margin: 5px 10px 10px 10px;
    padding: 0px;
}

p {
    margin: 10px;
    padding: 20px;
    background: #AEF2AC;
}

fieldset {
    margin: 10px;
    border: 1px solid #ddd;
}
</style>
</head>
<body>
<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

if (!is_siteadmin()) {
    echo 'Access denied!';
    exit();
}

/**
 * Form for importing repository properties
 * @param string $url The url to retrieve repository metadata
 * @return string
 *
 */
function get_form($url) {
    $form = '
        <form action="import_metadata.php" method="post" name="mdform">
            <fieldset>
                <legend>
                    Import application metadata
                </legend>
                <table>
                    <tr>
                        <td colspan="2"> Example metadata endpoints:
                        <br>
                        <table>
                            <tr>
                                <td>Repository: </td><td><a href="javascript:void();"
                                    onclick="document.forms[0].mdataurl.value=\'http://your-server-name/edu-sharing/metadata?format=lms\'">
                                    http://edu-sharing-server/edu-sharing/metadata?format=lms</a>
                                <br>
                                </td>
                            </tr>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="metadata">Metadata endpoint:</label></td>
                        <td>
                        <input type="text" size="80" id="metadata" name="mdataurl" value="' . $url . '">
                        <input type="submit" value="import">
                        </td>
                    </tr>
                </table>
            </fieldset>
        </form>';

    return $form;
}

$filename = '';


$metadataurl = optional_param('mdataurl', '', PARAM_NOTAGS);
if (!empty($metadataurl)) {

    try {

        $xml = new DOMDocument();

        libxml_use_internal_errors(true);

        if ($xml->load($metadataurl) == false) {
            echo ('<p style="background: #FF8170">could not load ' . $metadataurl .
                     ' please check url') . "<br></p>";
            echo get_form($metadataurl);
            exit();
        }

        $xml->preserveWhiteSpace = false;
        $xml->formatOutput = true;
        $entrys = $xml->getElementsByTagName('entry');
        foreach ($entrys as $entry) {
            set_config('repository_'.$entry->getAttribute('key'), $entry->nodeValue, 'edusharing');
        }

        require_once(dirname(__FILE__) . '/AppPropertyHelper.php');
        $modedusharingapppropertyhelper = new mod_edusharing_app_property_helper();
        $sslkeypair = $modedusharingapppropertyhelper->edusharing_get_ssl_keypair();

        set_config('application_host', $_SERVER['SERVER_ADDR'], 'edusharing');
        set_config('application_appid', uniqid('moodle_'), 'edusharing');
        set_config('application_type', 'LMS', 'edusharing');
        set_config('application_homerepid', get_config('edusharing', 'repository_appid'), 'edusharing');
        set_config('application_cc_gui_url', get_config('edusharing', 'repository_clientprotocol') . '://' .
            get_config('edusharing', 'repository_domain') . ':' .
            get_config('edusharing', 'repository_clientport') . '/edu-sharing/', 'edusharing');
        set_config('application_private_key', $sslkeypair['privateKey'], 'edusharing');
        set_config('application_public_key', $sslkeypair['publicKey'], 'edusharing');
        set_config('application_blowfishkey', 'thetestkey', 'edusharing');
        set_config('application_blowfishiv', 'initvect', 'edusharing');

        set_config('EDU_AUTH_KEY', 'username', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_USERID', 'userid', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_LASTNAME', 'lastname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', 'firstname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_EMAIL', 'email', 'edusharing');
        set_config('EDU_AUTH_AFFILIATION', $CFG->siteidentifier, 'edusharing');

        if (empty($sslkeypair['privateKey'])) {
            echo 'Generating of SSL keys failed. Please check your configuration.';
        } else {
            echo 'Import sucessfull.';
        }
        exit();
    } catch (Exception $e) {
        echo $e->getMessage();
        exit();
    }
}

echo get_form('');
exit();
