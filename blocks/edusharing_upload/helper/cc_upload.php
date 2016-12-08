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
 * Provide edu-sharing upload
 *
 * @package    block_edusharing_upload
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');

global $DB;
global $CFG;

require_once('../../../mod/edusharing/lib/cclib.php');
require_once('../../../mod/edusharing/lib.php');

require_sesskey();

$id = optional_param('id', 0, PARAM_INT);

if (!$id) {
    trigger_error(get_string('error_invalid_course_id', 'block_edusharing_upload'), E_USER_WARNING);
    exit();
}

$PAGE->set_url('/blocks/edusharing_upload/helper/cc_upload.php', array('id'  => $id));

$course = $DB->get_record('course', array('id'  => $id));
if (!$course) {
    trigger_error(get_string('error_course_not_found', 'block_edusharing_upload'), E_USER_WARNING);
    exit();
}

require_login($course->id);

echo $OUTPUT->header();

$ccauth = new mod_edusharing_web_service_factory();
$ticket = $ccauth->edusharing_authentication_get_ticket();
if (!$ticket) {
    exit();
}

$link = get_config('edusharing', 'application_cc_gui_url');
// link to the external cc-upload
$link .= '?mode=2';
$user = edusharing_get_auth_key();
$link .= '&user=' . urlencode($user);

$link .= '&ticket=' . urlencode($ticket);

$mylang = edusharing_get_current_users_language_code();
$link .= '&locale=' . urlencode($mylang);

$link .= '&reurl=' . urlencode($CFG->wwwroot . '/blocks/edusharing_upload/helper/close_iframe.php?course_id=' . $course->id);

// Open the external edu-sharingSearch page in iframe
?>

<div id="esContent" style="position: fixed; top: 0; left: 0; z-index: 9900;"></div>
<script src="<?php echo $CFG->wwwroot?>/mod/edusharing/js/jquery.min.js"></script>
<script>
$('html, body').css('overflow', 'hidden');
$('#esContent').width($(document).width());
$('#esContent').height($(document).height());
$('#esContent').html("<div id='closer' style='font-size: 1em; padding: 5px 20px 5px 20px; cursor: pointer; color: #000; background: #eee; '>" +
"<?php echo htmlentities(get_string('back_to', 'block_edusharing_upload'))?>&nbsp;\"<?php echo $COURSE->fullname?>\"</div><iframe id='childFrame' name='mainContent'" +
"src='<?php echo htmlentities($link)?>' width='100% ' height='100% ' scrolling='yes'  marginwidth='0' marginheight='0' frameborder='0'>&nbsp;</iframe>");
$('#closer').click(function() {window.location.href='<?php echo $_SERVER["HTTP_REFERER"]?>';})</script>

<?php
// ------------------------------------------------------------------------------------

exit();
