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
 * Callback script for repo
 *
 * Called from repository after selecting a node/resource in the opened popup window
 * Transfers the node-id into the Location field of the opener (edit resource window)
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');

$PAGE->set_url($CFG->wwwroot.$SCRIPT);
$PAGE->set_context(context_system::instance() );

echo $OUTPUT->header();

$eduresource = addslashes_js(optional_param('nodeId', '', PARAM_RAW));
$title = addslashes_js(optional_param('title', '', PARAM_RAW));
echo <<<content
<script type="text/javascript">
        window.top.setNode('$eduresource', '$title');
</script>
content;

