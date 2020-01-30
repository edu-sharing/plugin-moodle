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
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot.'/mod/edusharing/locallib.php');
?>
<html>
<head>
    <title>edu-sharing metadata import</title>
    <link rel="stylesheet" href="import_metadata_style.css" />
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&display=swap" rel="stylesheet">
</head>
<body>

<div class="h5p-header">
    <h1>Import metadata from an edu-sharing repository</h1>
</div>

<div class="wrap">

<?php

if (!is_siteadmin()) {
    echo '<h3>Access denied!</h3>';
    echo '<p>Please login with your admin account in moodle.</p>';
    exit();
}

if(isset($_POST['repoReg'])){
    callRepo($_POST['repoAdmin'], $_POST['repoPwd']);
    exit();
}

$filename = '';

$metadataurl = optional_param('mdataurl', '', PARAM_NOTAGS);
if (!empty($metadataurl)) {
    edusharing_import_metadata($metadataurl);
    echo getRepoForm();
    exit();
}

echo get_form();
echo getRepoForm();

echo '</div></body></html>';
exit();

function callRepo($user, $pwd){
    global $CFG;

    $repo_url = get_config('edusharing', 'application_cc_gui_url');
    $apiUrl = $repo_url.'rest/admin/v1/applications?url='.$CFG->wwwroot.'/mod/edusharing/metadata.php';
    $auth = $user.':'.$pwd;
    $answer = json_decode(callRepoAPI('PUT', $apiUrl, null, $auth), true);
    if ( isset($answer['appid']) ){
        echo('<h3 class="edu_success">Successfully registered the edusharing-moodle-plugin at: '.$repo_url.'</h3>');
    }else{
        echo('<h3 class="edu_error">ERROR: Could not register the edusharing-moodle-plugin at: '.$repo_url.'</h3>');
        if ( isset($answer['message']) ){
            echo '<p class="edu_error">'.$answer['message'].'</p>';
        }
        echo '<h3>Register the Moodle-Plugin in the Repository manually:</h3>';
        echo '
            <p class="edu_metadata"> To register the Moodle-PlugIn manually got to the 
            <a href="'.$repo_url.'" target="_blank">Repository</a> and open the "APPLICATIONS"-tab of the "Admin-Tools" interface.<br>
            Only the system administrator may use this tool.<br>
            Enter the URL of the Moodle you want to connect. The URL should look like this:  
            „[Moodle-install-directory]/mod/edusharing/metadata.php".<br>
            Click on "CONNECT" to register the LMS. You will be notified with a feedback message and your LMS instance 
            will appear as an entry in the list of registered applications.<br>
            If the automatic registration failed due to a connection issue caused by a proxy-server, you also need to 
            add the proxy-server IP-address as a "host_aliases"-attribute.
            </p>
        ';
    }
}

function getRepoForm(){
    $repo_url = get_config('edusharing', 'application_cc_gui_url');
    if (!empty($repo_url)){
        return '
            <form class="repo-reg" action="import_metadata.php" method="post">
                <h3>Try to register the edu-sharing moodle-plugin with a repository:</h3>
                <p>If your moodle is behind a proxy-server, this might not work and you have to register the plugin manually.</p>
                <div class="edu_metadata">
                    <div class="repo_input">
                        <p>Repo-URL:</p><input type="text" value="'.$repo_url.'" name=repoUrl />
                    </div>
                    <div class="repo_input">
                        <p>Repo-Admin-User:</p><input class="short_input" type="text" name="repoAdmin">
                        <p>Repo-Admin-Password:</p><input class="short_input" type="password" name="repoPwd">
                    </div>
                    <input class="btn" type="submit" value="Register Repo" name="repoReg">
                </div>            
            </form>
         ';
    }else{
        return false;
    }

}

/**
 * Form for importing repository properties
 * @param string $url The url to retrieve repository metadata
 * @return string
 *
 */
function get_form() {
    global $CFG;
    return '
        <form action="import_metadata.php" method="post" name="mdform">
            <h3>Enter your metadata endpoint here:</h3>
            <p>Hint: Just click on the example to copy it into the input-field.</p>
            <div class="edu_metadata">                
                <div class="edu_endpoint">
                    <p>Metadata-Endpoint:</p>
                    <input type="text" id="metadata" name="mdataurl" value="">
                    <input class="btn" type="submit" value="Import">
                </div>
                <div class="edu_example">
                    <p>(Example: <a href="javascript:void();"
                                   onclick="document.forms[0].mdataurl.value=\'http://your-server-name/edu-sharing/metadata?format=lms\'">
                                   http://your-server-name/edu-sharing/metadata?format=lms</a>)
                   </p>
                </div>
            </div>
        </form>
        <p>To export the edu-sharing plugin metadata use the following url: <span class="edu_export">' . $CFG->wwwroot . '/mod/edusharing/metadata.php</span></p>';
}
