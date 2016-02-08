<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 */

/*
 - called from the /blocks/cc_search block
 - auth against alfresco repos. (ticket handshake / user sync)
 - opens external edu-sharingSearch in iFrame
 */
require_once('../../../config.php');

global $DB;
global $CFG;
global $SESSION;

require_once('../../../mod/edusharing/lib/ESApp.php');
require_once('../../../mod/edusharing/lib/EsApplication.php');
require_once('../../../mod/edusharing/lib/EsApplications.php');
require_once('../../../mod/edusharing/conf/cs_conf.php');
require_once('../../../mod/edusharing/lib/cclib.php');
require_once('../../../mod/edusharing/lib.php');

//.oO get CC homeconf
$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$conf = $es->getHomeConf();
$propArray = $conf->prop_array;

$PAGE->set_url('/blocks/edusharing_search/helper/cc_search.php',array('id' => $_GET['id'],'search'=>'test')).

// Using http://127.0.0.1/moodle241/blocks/edusharing_search/helper/cc_search.php?id=1&search=test
//$PAGE->set_url('/mod/mymodulename/view.php', array('id' => $cm->id));


$id = optional_param('id', 0, PARAM_INT);      // course id
if ( ! $id ) {
    print_error("None or invalid course-id given.");
    exit();
}

$course = $DB->get_record('course', array('id' => $id));
if ( ! $course ) {
    print_error("Course not found.");
    exit();
}

require_login($course->id);

$navlinks = array();
$navlinks[] = array('name' => get_string('block_title', 'block_edusharing_search'), 'link' => null, 'type' => 'misc');
$navigation = build_navigation($navlinks);


//$PAGE->set_pagelayout('incourse');

print_header("", "edu-sharing", $navigation, "", "", true, "&nbsp;", "edu-sharing");
//print_heading();

//$OUTPUT->heading(get_string('block_title','block_edusharing_search')).
//echo $OUTPUT->heading(get_string('help'), 3, 'helptitle', 'uniqueid');

$ccauth = new CCWebServiceFactory($propArray['homerepid']);
$ticket = $ccauth->CCAuthenticationGetTicket($propArray['appid']);
if ( ! $ticket )
{
    print_error($ccauth->beautifyException($ticket));
  $OUTPUT->footer();
//   $OUTPUT->heading().
    exit;
}

if ( empty($propArray['cc_gui_url']) )
{
    trigger_error('No "cc_gui_url" configured.', E_ERROR);
}

$link = $propArray['cc_gui_url']; // link to the external cc-search
$link .= '?mode=0';
$user = get_edu_auth_key();
$link .= '&user='.urlencode($user);
$link .= '&ticket='.urlencode($ticket);

$_my_lang = _edusharing_get_current_users_language_code();
$link .= '&locale=' . urlencode($_my_lang);

$link .= '&p_startsearch=1';   ////////// 0=suche  / 1=workspace

$search = trim(optional_param('search', '', PARAM_NOTAGS)); // query for the external cc-search
if (!empty($search)) {
    $link .= '&p_searchtext='.urlencode($search);
}

// -------------------------------------------------------------------------
//  open the external edu-sharingSearch page in iframe
// -------------------------------------------------------------------------

?>



<div id="esContent" style="position: fixed; top: 0; left: 0; z-index: 5000;"></div>
<script src="<?php echo $CFG->wwwroot?>/mod/edusharing/js/jquery.min.js"></script>
<script>
    $('html, body').css('overflow', 'hidden');
    $('#esContent').width($(document).width());
    $('#esContent').height($(document).height());
    $('#esContent').html("<iframe id='childFrame' name='mainContent' src='<?php echo htmlentities($link)?>' width='100%' height='100%' scrolling='yes'  marginwidth='0' marginheight='0' frameborder='0'>&nbsp;</iframe><div id='closer' style='position:fixed; z-index: 501; min-width: 100px; top: 0; left: 50%; background: #1a5170; text-align: center; font-size: 1em; padding: 5px 20px 5px 20px; cursor: pointer;  -webkit-border-bottom-left-radius: 5px; -webkit-border-bottom-right-radius: 5px;-khtml-border-bottom-left-radius: 5px;-khtml-border-bottom-right-radius: 5px;-moz-border-radius-bottomleft: 5px;-moz-border-radius-bottomright: 5px;border-bottom-left-radius: 5px;border-bottom-right-radius: 5px;color: #fff;'>◄&nbsp;&nbsp;Zur&uuml;ck zu &nbsp;\"<?php echo $COURSE->fullname?>\"</div>");
    var margin_left = $('#closer').width()/2;
    $('#closer').css('margin-left', -margin_left+'px');
    $('#closer').click(function(){window.location.href='<?php echo $_SERVER["HTTP_REFERER"]?>';})
</script>
<?php

// -------------------------------------------------------------------------

$OUTPUT->footer();
exit();
