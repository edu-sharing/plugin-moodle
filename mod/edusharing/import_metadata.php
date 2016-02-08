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
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

?>

<html>
<head>
<title>edu-sharing metadata import</title>
<style type="text/css" id="vbulletin_css">
	body {
		background: #e4f3f9;
		color: #000000;
		font: 11pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
		margin: 5px 10px 10px 10px;
		padding: 0px;
	}
	table {
		background: #e4f3f9;
		color: #000000;
		font: 10pt verdana, geneva, lucida, 'lucida grande', arial, helvetica, sans-serif;
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

		// customize
		define('import_metadata', true);

		require_once(dirname(dirname(dirname(__FILE__))).'/config.php');

		if(!is_siteadmin()) {
		  echo 'Access denied!';
		  exit();
        }

		define ('CC_CONF_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
		define ('CC_CONF_APPFILE','ccapp-registry.properties.xml');

		if (!import_metadata) die('metadata import disabled');

		function getForm($url){

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
								<td>Repository: </td><td><a href="javascript:void();" onclick="document.forms[0].mdataurl.value=\'http://your-server-name/edu-sharing/metadata?format=lms\'">http://edu-sharing-server/edu-sharing/metadata?format=lms</a>
								<br>
								</td>
							</tr>
						</td>
					</tr>
					<tr>
						<td><label for="metadata">Metadata endpoint:</label></td>
						<td>
						<input type="text" size="80" id="metadata" name="mdataurl" value="'.$url.'">
						<input type="submit" value="import">
						</td>
					</tr>
				</table>
			</fieldset>
		</form>';

		return $form;

		}

		$filename='';

if (!empty( $_POST['mdataurl'])) {

    try {
    
        $xml = new DOMDocument();
    
        libxml_use_internal_errors(true);
    
        if ($xml->load($_POST['mdataurl']) == false){
            echo ('<p style="background: #FF8170">could not load '.$_POST['mdataurl'].' please check url')."<br></p>";
            echo getForm($_POST['mdataurl']);
            exit();
        }
    
    
        $xml -> preserveWhiteSpace = false;
        $xml -> formatOutput = true;
        $entrys = $xml->getElementsByTagName('entry');
        $repProperties = new stdClass();
        foreach ($entrys as $entry){
            $key = $entry -> getAttribute('key');
            $repProperties -> $key = $entry -> nodeValue;
        }
    
        set_config('repProperties', json_encode($repProperties), 'edusharing');
        
        $homeAppProperties = new stdClass();
        require_once(dirname(__FILE__) . '/mod_edusharing_app_property_helper.php');
        $mod_edusharing_app_property_helper = new mod_edusharing_app_property_helper();
        $sslKeypair = $mod_edusharing_app_property_helper -> mod_edusharing_get_ssl_keypair();
        
        $homeAppProperties -> host = $_SERVER['SERVER_ADDR'];
        $homeAppProperties -> appid = uniqid('moodle_');
        $homeAppProperties -> type = 'LMS';
        $homeAppProperties -> homerepid = $repProperties -> appid;
        $homeAppProperties -> cc_gui_url = $repProperties -> clientprotocol . '://' . $repProperties -> domain . ':' . $repProperties -> clientport . '/edu-sharing/';
        $homeAppProperties -> private_key = $sslKeypair['privateKey'];
        $homeAppProperties -> public_key = $sslKeypair['publicKey'];
        $homeAppProperties -> signatureRedirector = $mod_edusharing_app_property_helper -> mod_edusharing_get_signatureRedirector();
        
        set_config('appProperties', json_encode($homeAppProperties), 'edusharing');
        
        set_config('EDU_AUTH_KEY', 'username', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_USERID', 'userid', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_LASTNAME', 'lastname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_FIRSTNAME', 'firstname', 'edusharing');
        set_config('EDU_AUTH_PARAM_NAME_EMAIL', 'email', 'edusharing');
        set_config('EDU_AUTH_AFFILIATION', $CFG -> siteidentifier, 'edusharing');
        
        if(empty($sslKeypair['privateKey']))
            echo 'Generating of SSL keys failed. Please check your configuration.';
        else
            echo 'Import sucessfull.';
        exit();
    
    } catch (Exception $e) {
        echo $e -> getMessage();
        exit();
    }

};

echo getForm('');
exit();
