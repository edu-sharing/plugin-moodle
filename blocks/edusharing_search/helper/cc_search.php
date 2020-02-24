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
 * Provide edu-sharing search
 *
 * @package    block_edusharing_search
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../../config.php');

global $DB;
global $CFG;
global $USER;
global $SESSION;

require_once('../../../mod/edusharing/lib/cclib.php');
require_once('../../../mod/edusharing/lib.php');

require_sesskey();

$id = optional_param('id', 0, PARAM_INT);
if ( ! $id ) {
    trigger_error(get_string('error_invalid_course_id', 'block_edusharing_search'), E_USER_WARNING);
    exit();
}

$PAGE->set_url('/blocks/edusharing_search/helper/cc_search.php', array('id' => $id, 'search' => ''));

$course = $DB->get_record('course', array('id'  => $id));
if ( ! $course ) {
    trigger_error(get_string('error_course_not_found', 'block_edusharing_search'), E_USER_WARNING);
    exit();
}

require_login($course->id);

echo $OUTPUT->header();

$ccauth = new mod_edusharing_web_service_factory();
$ticket = $ccauth->edusharing_authentication_get_ticket();
if ( ! $ticket ) {
    exit();
}

$link = trim(get_config('edusharing', 'application_cc_gui_url'), '/');
$search = trim(optional_param('search', '', PARAM_NOTAGS)); // query for the external cc-search
if(version_compare(get_config('edusharing', 'repository_version'), '4' ) >= 0) {
    $link .= '/components/search';
    $link .= '?locale=' . current_language();
    if (!empty($search)) {
        $link .= '&query='.urlencode($search);
    }
} else {
    $link .= '/?mode=0';
    $user = edusharing_get_auth_key();
    $link .= '&user='.urlencode($user);
    $link .= '&locale=' . current_language();
    $link .= '&p_startsearch=1';
    if (!empty($search)) {
        $link .= '&p_searchtext='.urlencode($search);
    }
}
$link .= '&ticket='.urlencode($ticket);

// Open the external edu-sharingSearch page in iframe
?>

<div id="esContent">
    <div class="esOuter">
        <div id="closer"><a href="<?php echo $_SERVER['HTTP_REFERER'];?>">&times;</a></div>
        <iframe id="childFrame" name="mainContent" src="<?php echo htmlentities($link);?>" width="100%" height="100%" scrolling="yes"
                marginwidth="0" marginheight="0" frameborder="0">
        </iframe>
    </div>
</div>

<script>
    document.getElementById("esContent").style.opacity = '1';
</script>

<?php
$OUTPUT->footer();
exit();
