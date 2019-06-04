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
 * edu-sharing insert dialog
 *
 * @package    editor_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . "/../../../../config.php");
require_once($CFG->dirroot.'/lib/setup.php');

require_login();
require_sesskey();

global $DB;
global $CFG;
global $COURSE;
global $USER;
global $SESSION;

require_once($CFG->dirroot.'/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot.'/mod/edusharing/lib.php');

$tinymce = get_texteditor('tinymce');
if ( ! $tinymce ) {
    throw new RuntimeException(get_string('error_get_tinymce', 'editor_edusharing'));
}

if ( empty($CFG->yui3version) ) {
    throw new RuntimeException(get_string('error_determine_yui', 'editor_edusharing'));
}

?><html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlentities(get_string('dialog_title', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></title>

    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">

    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/tinymce/tiny_mce/'.$tinymce->version.'/tiny_mce_popup.js', ENT_COMPAT, 'utf-8') ?>">
    </script>

    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/js/edusharing.js?' .
            filemtime($CFG->libdir.'/editor/edusharing/js/edusharing.js'), ENT_COMPAT, 'utf-8') ?>"></script>
    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/js/dialog.js?' .
            filemtime($CFG->libdir.'/editor/edusharing/js/dialog.js'), ENT_COMPAT, 'utf-8') ?>"></script>

    <link rel="stylesheet" media="all" href="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/dialog/css/edu.css', ENT_COMPAT, 'utf-8') ?>">

</head>

<body class="edusharing_body">

<form>
<?php

$edusharing = new stdClass();
$edusharing->object_url = '';
$edusharing->course_id = $COURSE->id;
$edusharing->id = 0;
$edusharing->resource_type = '';
$edusharing->resource_version = '';
$edusharing->title = '';
$edusharing->window_width = '';
$edusharing->window_height = '';
$edusharing->mimetype = '';
$edusharing->window_float = 'none';
$edusharing->window_versionshow = 'latest';
$edusharing->repo_type = '';
$edusharing->window_version = '';
$edusharing->mediatype = '';

$repositoryid = get_config('edusharing', 'application_homerepid');
$resourceid = optional_param('resource_id', 0, PARAM_INT);
if ( ! empty($resourceid) ) {

    $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id'  => $resourceid));
    if ( ! $edusharing ) {
        header('HTTP/1.1 500 Internal Server Error');
        throw new Exception(get_string('error_loading_resource', 'editor_edusharing'));
    }

    $repositoryid = edusharing_get_repository_id_from_url($edusharing->object_url);
    if ( ! $repositoryid ) {
        header('HTTP/1.1 500 Internal Server Error');
        throw new Exception(get_string('error_get_repository', 'editor_edusharing'));
    }
}


$ccauth = new mod_edusharing_web_service_factory();
$ticket = $ccauth->edusharing_authentication_get_ticket();
$link = trim(get_config('edusharing', 'application_cc_gui_url'), '/');
if(version_compare(get_config('edusharing', 'repository_version'), '4' ) >= 0) {
    $link .= '/components/search';
    $link .= '?locale=' . current_language();
} else {
    $link .= '/search';
    $user = edusharing_get_auth_key();
    $link .= '?user=' . urlencode($user);
    $language = current_language();
    $link .= '&locale=' . $language;
}

$link .= '&ticket='.$ticket;
$link .= '&applyDirectories=true'; // used in 4.2 or higher, allow directories to be included
$link .= '&reurl='.urlencode($CFG->wwwroot."/lib/editor/edusharing/dialog/populate.php?");

$previewurl = get_config('edusharing', 'application_cc_gui_url') . 'preview';

/**
 * Return some dummy text
 * @return string
 */
function get_preview_text() {
    return 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat,
            sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem
            ipsum dolor sit amet.';
}

?>
    <input type="hidden" name="preview_url" value="<?php echo htmlspecialchars($previewurl, ENT_COMPAT, 'utf-8'); ?>" />
    <input type="hidden" name="edu_ticket" value="<?php echo htmlspecialchars($ticket, ENT_COMPAT, 'utf-8'); ?>" />

    <input type="hidden"  maxlength="30" name="mimetype" id="mimetype" />
    <input type="hidden"  maxlength="30" name="ratio" id="ratio" />
    <input type="hidden"  maxlength="30" name="window_version" id="window_version" />
    <input type="hidden" maxlength="30" name="resourcetype" id="resourcetype" value="<?php echo htmlspecialchars($edusharing->resource_type, ENT_COMPAT, 'utf-8') ?>" />

    <input type="hidden" maxlength="30" size="15" name="resourceversion" id="resourceversion" />
    <input type="hidden" maxlength="30" size="30" name="repotype" id="repotype" />
    <input type="hidden" maxlength="30" size="30" name="mediatype" id="mediatype" />
    <input type="hidden" maxlength="30" size="15" name="resource_id" id="resource_id"  value="<?php echo htmlspecialchars($edusharing->resource_id, ENT_COMPAT, 'utf-8') ?>" />
    <input type="hidden" maxlength="40" size="35" name="ticket" id="ticket"  value="<?php echo htmlspecialchars($ticket, ENT_COMPAT, 'utf-8') ?>" />


<input type="hidden" name="object_url" id="object_url" value="<?php echo htmlspecialchars($edusharing->object_url, ENT_COMPAT, 'utf-8') ?>" />
    <div id="eduHeader"><img src="" id="headerIcon"><span id="headerTitle"></span></div>
    <div id="directory" style="display: none">
        <p><?php echo htmlspecialchars(get_string('directory1', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></p>
        <p><?php echo htmlspecialchars(get_string('directory2', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></p>
    </div>

<div id="form_wrapper" style="float:left">
    <table>
        <tr>
            <td><span id="titleLabel"><?php echo htmlentities(get_string('caption', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></span></td>
            <td><input type="text" maxlength="50" style="width: 200px" name="title" id="title" value="<?php echo htmlspecialchars($edusharing->title, ENT_COMPAT, 'utf-8') ?>"></input>
            </td>
        </tr>
        <tr class="versionShowTr">
            <td><?php echo  htmlentities(get_string('version', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>
                <input type="radio" value="latest" name="window_versionshow" <?php echo ($edusharing->window_versionshow == 'latest') ? 'checked="checked"' : ''?> />
                <?php echo  htmlentities(get_string('versionLatest', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                <input type="radio" value="current" name="window_versionshow" <?php echo ($edusharing->window_versionshow == 'current') ? 'checked="checked"' : ''?> />
                <?php echo  htmlentities(get_string('versionCurrent', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
            </td>
        </tr>

        <tr id="floatTr">
            <td><?php echo  htmlentities(get_string('float', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>

                <input type="radio" value="none" name="window_float" <?php echo ($edusharing->window_float == 'none') ? 'checked="checked"' : ''?>
                       onClick="editor_edusharing_handle_click(this)" />
                <?php echo  htmlentities(get_string('floatNone', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>

                <input type="radio" value="left" name="window_float" <?php echo ($edusharing->window_float == 'left') ? 'checked="checked"' : ''?>
                onClick="editor_edusharing_handle_click(this)" /><?php echo  htmlentities(get_string('floatLeft', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>

                <input type="radio" value="right" name="window_float" <?php echo ($edusharing->window_float == 'right') ? 'checked="checked"' : ''?>
                onClick="editor_edusharing_handle_click(this)" />
                <?php echo  htmlentities(get_string('floatRight', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>

                <!-- <input type="radio" value="inline" name="window_float" <?php echo ($edusharing->window_float == 'inline') ? 'checked="checked"' : ''?>
                onClick="editor_edusharing_handle_click(this)" />
                <?php echo  htmlentities(get_string('floatInline', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?> -->
            </td>
        </tr>
           <tr class="dimension">
            <td><?php echo htmlentities(get_string('window_width', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td><input type="text" maxlength="4" size="5" name="window_width" id="window_width"
            value="<?php echo htmlspecialchars($edusharing->window_width, ENT_COMPAT, 'utf-8') ?>" onKeyUp="editor_edusharing_set_height()" />&nbsp;px</td>
        </tr>
         <tr class="dimension heightProp">
            <td><?php echo htmlentities(get_string('window_height', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td><input type="text" maxlength="4" size="5" name="window_height" id="window_height"
            value="<?php echo htmlspecialchars($edusharing->window_height, ENT_COMPAT, 'utf-8') ?>" onKeyUp="editor_edusharing_set_width()" />&nbsp;px</td>
        </tr>
    </table>
    </div>

    <div id="preview">
        <?php echo  get_preview_text()?>
        <div id="preview_resource_wrapper"></div>
        <?php echo  get_preview_text()?>

    </div>
    <div style="clear:both" class="mceActionPanel edusharing_mceActionPanel">
        <input type="button" id="insert" name="insert" class="edusharing_dialog_button edusharing_dialog_button_insert" value="<?php echo htmlspecialchars(get_string('insert', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>"
               onclick="edusharingDialog.on_click_insert(this.form);" />
        <input type="button" id="cancel" name="cancel" class="edusharing_dialog_button edusharing_dialog_button_cancel" value="<?php echo htmlspecialchars(get_string('cancel', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>"
               onclick="edusharingDialog.on_click_cancel();" />
    </div>

</form>

<iframe id="eduframe" src="<?php echo htmlspecialchars($link, ENT_COMPAT, 'utf-8'); ?>"></iframe>

</body>

<script type="text/javascript">

    function editor_edusharing_show_repository_search() {
        var eduframe = document.getElementById('eduframe');
        eduframe.src = '<?php echo $link ?>';
        eduframe.style.display = 'block';
        editor_edusharing_enlarge_dialog();
    }

    function editor_edusharing_set_width() {
        document.getElementById('window_width').value = Math.round(document.getElementById('window_height').value / editor_edusharing_get_ratio());
    }

    function editor_edusharing_set_height() {
        document.getElementById('window_height').value = Math.round(document.getElementById('window_width').value * editor_edusharing_get_ratio());
    }

    function editor_edusharing_get_ratio() {
        return document.getElementById('ratio').value;
    }

    function editor_edusharing_refresh_preview(float) {
        style = tinymce.plugins.edusharing.getStyle(float);
        width = document.getElementById('preview_resource_wrapper').style.width;
        document.getElementById('preview_resource_wrapper').style = style;
        document.getElementById('preview_resource_wrapper').style.width = width;
    }

    function editor_edusharing_handle_click(radio) {
        editor_edusharing_refresh_preview(radio.value);
    }

    function editor_edusharing_get_resource_preview() {

        // splitting object-url to get object-id
        var repository_id = '<?php echo $repositoryid; ?>';
        var object_url_parts = document.getElementById('object_url').value.split('/');
        var object_id = object_url_parts[3];

        var remoterepo ='';

        if ( repository_id != object_url_parts[2]) {
              remoterepo = '&repoId='+object_url_parts[2];
        }

        var preview_url = document.getElementsByName('preview_url')[0].value;
        preview_url = preview_url.concat('?nodeId=' + object_id);
        preview_url = preview_url.concat(remoterepo);
        preview_url = preview_url.concat('&ticket=' + document.getElementsByName('edu_ticket')[0].value);

        return preview_url;
    }

    function editor_edusharing_set_preview_content() {
        mimeSwitchHelper = '';
        mimetype = document.getElementById('mimetype').value;
        mediatype = document.getElementById('mediatype').value;
        if(mediatype == 'directory') {
            editor_edusharing_shrink_dialog('400','260');
            editor_edusharing_vis_directory();
            return;
        }

        if(mediatype.indexOf('tool_object') !== -1)
            mimeSwitchHelper = 'tool';
        else if (mimetype.indexOf('jpg') !== -1 || mimetype.indexOf('jpeg') !== -1 || mimetype.indexOf('gif') !== -1 || mimetype.indexOf('png') !== -1)
           mimeSwitchHelper = 'image';
        else if (mimetype.indexOf('audio') !== -1)
           mimeSwitchHelper = 'audio';
        else if (mimetype.indexOf('video') !== -1)
            mimeSwitchHelper = 'video';
        else if (document.getElementById('repotype').value == 'YOUTUBE')
            mimeSwitchHelper = 'youtube';
        else
            mimeSwitchHelper = 'textlike';

        switch(mimeSwitchHelper) {
            case 'tool' : content = '<img src="'+editor_edusharing_get_resource_preview()+'" width=80/><br/>' +
                '<?php echo htmlspecialchars(get_string('titleAuthorLicense', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?><br/>'; break;
            case 'image' : content = '<img src="'+editor_edusharing_get_resource_preview()+'" width=80/><br/>' +
                '<?php echo htmlspecialchars(get_string('titleAuthorLicense', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?><br/>'; break;
            case 'youtube' : content = '<img src="'+editor_edusharing_get_resource_preview()+'" width=80/><br/>' +
                '<?php echo htmlspecialchars(get_string('titleAuthorLicense', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?><br/>'; break;
            case 'video' : content = '<img src="'+editor_edusharing_get_resource_preview()+'" width=80/><br/>' +
                '<?php echo htmlspecialchars(get_string('titleAuthorLicense', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?><br/>'; break;
            case 'audio' : content = '<img src="../images/audio.png" width=100/><br/>Titel/Autor/Lizenz<br/>'; break;
            default: content = '' ;
        }

        if (mimeSwitchHelper != 'textlike') {
            document.getElementById('preview_resource_wrapper').style.width = '80px';
        } else {
            document.getElementById('preview_resource_wrapper').style.width = 'auto';
        }

        content += '<span id="textpreview"></span>';

        document.getElementById('preview_resource_wrapper').innerHTML = content;

        if (mimeSwitchHelper == 'textlike') {
            setTextPreview();
            document.getElementById('textpreview').style.color = '#00F';
        }

        editor_edusharing_vis_dimension_inputs(mimeSwitchHelper);
        editor_edusharing_set_title_options(mimeSwitchHelper);
        editor_edusharing_vis_version_inputs();
    }

    function editor_edusharing_vis_directory() {
        document.getElementById('form_wrapper').style.display = 'none';
        document.getElementById('preview').style.display = 'none';
        document.getElementById('directory').style.display = 'block';
        document.getElementById('headerIcon').src = '<?php echo $CFG->wwwroot?>/lib/editor/edusharing/images/edudirectory.svg';
        document.getElementById('mediatype').value = 'directory';
    }

    function setTextPreview() {
           document.getElementById('textpreview').innerHTML = document.getElementById('title').value;
    }

    function editor_edusharing_vis_version_inputs() {
        if (document.getElementById('repotype').value == 'YOUTUBE') {
            document.getElementsByClassName('versionShowTr')[0].style.visibility = 'hidden';
        } else {
            document.getElementsByClassName('versionShowTr')[0].style.visibility = 'visible';
        }

    }

    function editor_edusharing_set_title_options(mimeSwitchHelper) {
        titleLabel = document.getElementById('titleLabel');
        if (mimeSwitchHelper == 'textlike') {
            titleLabel.innerHTML = "<?php echo htmlspecialchars(get_string('linktext', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>";
        } else {
            titleLabel.innerHTML = "<?php echo htmlspecialchars(get_string('caption', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>";
            document.getElementById('title').value = "";
            document.getElementById('textpreview').value = "";
        }
    }

    function editor_edusharing_vis_dimension_inputs(mimeSwitchHelper) {

       if (mimeSwitchHelper == 'image' || mimeSwitchHelper == 'tool') {
           var dimensionsSet = document.getElementsByClassName('dimension');
           for(var i = 0; i < dimensionsSet.length; i++) {
                dimensionsSet[i].style.visibility = 'visible';
            }
       } else if (mimeSwitchHelper == 'video' || mimeSwitchHelper == 'youtube') {
           var dimensionsSet = document.getElementsByClassName('dimension');
           for(var i = 0; i < dimensionsSet.length; i++) {
                dimensionsSet[i].style.visibility = 'visible';
            }
           var dimensionsSet = document.getElementsByClassName('heightProp');
           for(var i = 0; i < dimensionsSet.length; i++) {
                dimensionsSet[i].style.visibility = 'hidden';
            }
       } else {
           var dimensionsSet = document.getElementsByClassName('dimension');
           for(var i = 0; i < dimensionsSet.length; i++) {
                dimensionsSet[i].style.visibility = 'hidden';
            }
       }

    }


    function editor_edusharing_shrink_dialog(width, height) {

        var width = (typeof width === 'undefined') ? '580' : width;
        var height = (typeof height === 'undefined') ? '460' : height;

        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].style.width = width + 'px';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].style.height = height + 'px';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].getElementsByTagName('iframe')[0].style.width = '100%';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].getElementsByTagName('iframe')[0].style.height = '100%';
    }

    function editor_edusharing_enlarge_dialog() {
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].style.width = parent.parent.document.documentElement.clientWidth * 0.8 + 'px';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].style.height = parent.parent.document.documentElement.clientHeight * 0.8 + 'px';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].getElementsByTagName('iframe')[0].style.width =
            parent.parent.document.documentElement.clientWidth * 0.8 + 'px';
        parent.parent.document.querySelectorAll('div[id^="mce_inlinepopups_"]')[0].getElementsByTagName('iframe')[0].style.height =
            parent.parent.document.documentElement.clientHeight * 0.8 + 'px';
    }

    onload = function () {
        title = document.getElementById('title');
        title.oninput = function() {
            setTextPreview();
        }
        title.onchange = title.oninput;
        title.onkeypress = title.oninput;
        title.onpaste = title.oninput;
        title.onpropertychange = title.oninput;
    }


</script>

</html>
