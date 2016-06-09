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

/*
    - called from ALFRESCO after selecting a node/resource in the opened popup window
    - transfers the node-id into the Location field of the opener (edit resource window)
    - closes popup
*/

/**
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$PAGE->set_url($CFG->wwwroot.$SCRIPT);
$PAGE->set_context(context_system::instance() );

echo $OUTPUT->header();


$eduResource = addslashes_js(optional_param('nodeId', '', PARAM_RAW));

echo <<<content
This page should have populated the add resource form with the url to the Repository item.<br /><br />
<a href="#" onclick="window.close();">If this window does not close on its own, please click here.</a>
<script type="text/javascript">
    try{
        opener.document.getElementById('id_object_url').value = '$eduResource';
        opener.focus();
    } catch (err) {}
    window.close();
</script>
content;

