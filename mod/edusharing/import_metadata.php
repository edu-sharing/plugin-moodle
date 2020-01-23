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
    exit();
}

$filename = '';

$metadataurl = optional_param('mdataurl', '', PARAM_NOTAGS);
if (!empty($metadataurl)) {
    edusharing_import_metadata($metadataurl);
    exit();
}

echo get_form('');
echo '</div></body></html>';
exit();

/**
 * Form for importing repository properties
 * @param string $url The url to retrieve repository metadata
 * @return string
 *
 */
function get_form($url) {
    global $CFG;
    return '
        <form action="import_metadata.php" method="post" name="mdform">
            <h3>Enter your metadata Endpoint here:</h3>
            <p>Hint: Just click on the example to copy it into the input-field.</p>
            <div class="edu_metadata">                
                <div class="edu_endpoint">
                    <p>Metadata endpoint:</p>
                    <input type="text" id="metadata" name="mdataurl" value="' . $url . '">
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
