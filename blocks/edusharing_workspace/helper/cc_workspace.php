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

/*
 - called from the /blocks/cc_workspace block
 - auth against alfresco repos. (ticket handshake / user sync)
 - opens external edu-sharingWorkspace in iFrame
 */

/**
 * Get workspave within iframe
 *
 * @package    block_edusharing_workspace
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

global $DB;
global $CFG;
global $PAGE;
global $USER;
global $SESSION;

require_once('../../../mod/edusharing/lib/cclib.php');
require_once('../../../mod/edusharing/lib.php');

require_sesskey();

$id = optional_param('id', 0, PARAM_INT);
// course id
if (!$id) {
    trigger_error(get_string('error_invalid_course_id', 'block_edusharing_workspace'), E_USER_WARNING);
    exit();
}

$PAGE->set_url('/blocks/edusharing_workspace/helper/cc_workspace.php', array('id'  => $id));

$course = $DB->get_record('course', array('id'  => $id));
if (!$course) {
    trigger_error(get_string('error_course_not_found', 'block_edusharing_workspace'), E_USER_WARNING);
    exit();
}

require_login($course->id);

echo $OUTPUT->header();

$ccauth = new mod_edusharing_web_service_factory();
$ticket = $ccauth->edusharing_authentication_get_ticket();
if (!$ticket) {
    exit();
}

$link = trim(get_config('edusharing', 'application_cc_gui_url'), '/');
$mylang = edusharing_get_current_users_language_code();
if(version_compare(get_config('edusharing', 'repository_version'), '4' ) >= 0) {
    $link .= '?locale=' . $mylang;
} else {
    $link .= '/?mode=1';
    $user = edusharing_get_auth_key();
    $link .= '&user=' . urlencode($user);
    $link .= '&locale=' . $mylang;
}

$link .= '&ticket=' . urlencode($ticket);

global $COURSE;

// Open the external edu-sharingSearch page in iframe
?>

<div id="esContent" style="position: fixed; top: 0; left: 0; z-index: 5000;"></div>
<script src="<?php echo $CFG->wwwroot?>/mod/edusharing/js/jquery.min.js"></script>
<script>
$('html, body').css('overflow', 'hidden');
$('#esContent').width($(document).width());
$('#esContent').height($(document).height());
$('#esContent').html("<div id='closer' style='font-size: 1em; padding: 5px 20px 5px 20px; cursor: pointer; color: #000; background: #eee; '>" +
"<?php echo htmlentities(get_string('back_to', 'block_edusharing_workspace'))?>&nbsp;\"<?php echo $COURSE->fullname?>\"</div><iframe id='childFrame' name='mainContent'" +
"src='<?php echo htmlentities($link)?>' width='100% ' height='100% ' scrolling='yes'  marginwidth='0' marginheight='0' frameborder='0'>&nbsp;</iframe>");
$('#closer').click(function() {window.location.href='<?php echo $_SERVER["HTTP_REFERER"]?>';})</script>

<?php
// ------------------------------------------------------------------------------------

exit();
