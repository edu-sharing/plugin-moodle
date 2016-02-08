<?php

require_once(dirname(__FILE__) . "/../../../../config.php");
require_once($CFG->dirroot.'/lib/setup.php');

session_get_instance();
require_login();

global $DB;
global $CFG;
global $COURSE;
global $SESSION;

require_once($CFG->dirroot.'/mod/edusharing/lib/ESApp.php');
require_once($CFG->dirroot.'/mod/edusharing/lib/EsApplication.php');
require_once($CFG->dirroot.'/mod/edusharing/lib/EsApplications.php');
require_once($CFG->dirroot.'/mod/edusharing/conf/cs_conf.php');
require_once($CFG->dirroot.'/mod/edusharing/lib/cclib.php');
require_once($CFG->dirroot.'/mod/edusharing/lib.php');

$tinymce = get_texteditor('tinymce');
if ( ! $tinymce )
{
    throw new RuntimeException('Could not get_texteditor("tinymce") for version-information.');
}

if ( empty($CFG->yui3version) )
{
    throw new RuntimeException('Could not determine installed YUI-version.');
}

?><html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <title><?php echo htmlentities(get_string('dialog_title', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></title>
    
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    
    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/yui/'.$CFG->yui3version.'/build/yui/yui.js', ENT_COMPAT, 'utf-8') ?>"></script>
    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/tinymce/tiny_mce/'.$tinymce->version.'/tiny_mce_popup.js', ENT_COMPAT, 'utf-8') ?>"></script>

    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/js/edusharing.js', ENT_COMPAT, 'utf-8') ?>"></script>
    <script type="text/javascript" src="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/js/dialog.js', ENT_COMPAT, 'utf-8') ?>"></script>
    
    <link rel="stylesheet" media="all" href="<?php echo htmlentities($CFG->wwwroot.'/lib/editor/edusharing/dialog/css/edu.css', ENT_COMPAT, 'utf-8') ?>">

</head>

<body">

<form>
    <h2><?php echo htmlentities(get_string('dialog_title', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></h2>
    <br/>
    <p><?php echo htmlentities(get_string('dialog_infomsg', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></p>
    <br/>
<?php

//.oO get CC homeconf
$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$conf = $es->getHomeConf();
$propArray = $conf->prop_array;

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
$edusharing -> window_float = 'none';
$edusharing -> window_versionshow = 'latest';
$edusharing -> repo_type = '';

$repository_id = $propArray['homerepid'];
if ( ! $repository_id )
{
    header('HTTP/1.1 500 Internal Server Error');
    throw new Exception('No home-repository configured.');
}

if ( ! empty($_GET['resource_id']) )
{
    $resource_id = $_GET['resource_id'];

    $edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => $resource_id));
    if ( ! $edusharing ) {
        header('HTTP/1.1 500 Internal Server Error');
        throw new Exception('Error loading edusharing-resource.');
    }

    $repository_id = _edusharing_get_repository_id_from_url($edusharing->object_url);
    if ( ! $repository_id ) {
        header('HTTP/1.1 500 Internal Server Error');
        throw new Exception('Error reading repository-id from object-url.');
    }
}


$ccauth = new CCWebServiceFactory($repository_id);
$ticket = $ccauth->CCAuthenticationGetTicket($propArray['appid']);
if ( ! $ticket )
{
    print_error($ccauth->beautifyException($ticket));
    print_footer("edu-sharing");
    exit;
}

if ( empty($propArray['cc_gui_url']) )
{
    trigger_error('No "cc_gui_url" configured.', E_ERROR);
}

$link = $propArray['cc_gui_url']; // link to the external cc-search
$link .= '?mode=0';

$user = $_SESSION["USER"]->email;
$link .= '&user='.urlencode($user);

$link .= '&ticket='.urlencode($ticket);

$language = _edusharing_get_current_users_language_code();
if ( $language )
{
    $link .= '&locale=' . urlencode($language);
}

$search = trim(optional_param('search', '', PARAM_NOTAGS)); // query for the external cc-search
if (!empty($search)) {
    $link .= '&p_searchtext='.urlencode($search);
}

$link .= '&reurl='.urlencode($CFG->wwwroot."/lib/editor/edusharing/dialog/populate.php?");


//
$repository_conf = $es->getAppByID($repository_id);
if ( ! $repository_conf ) {
    error_log('Error loading config for "'.$repository_id.'".');
}

$alfresco_webservice_url = $repository_conf->prop_array['alfresco_webservice_url'];
if ( ! $alfresco_webservice_url ) {
    error_log('No alfresco_base_url for "'.$repository_id.'".');
}

function getPreviewText($short) {
    if($short)
        return 'Lorem ipsum dolor';
    return 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';
}

//
?>
    <input type="hidden" name="alfresco_base_url" value="<?php echo htmlspecialchars($alfresco_webservice_url, ENT_COMPAT, 'utf-8'); ?>" />
    <input type="hidden" name="alfresco_ticket" value="<?php echo htmlspecialchars($ticket, ENT_COMPAT, 'utf-8'); ?>" />

    <input type="hidden"  maxlength="30" size="15" name="mimetype" id="mimetype" />
    <input type="hidden"  maxlength="30" size="15" name="ratio" id="ratio" />
    <input type="hidden"  maxlength="30" size="15" name="window_version" id="window_version" />
    <input type="hidden" maxlength="30" size="15" name="resourcetype" id="resourcetype" value="<?php echo htmlspecialchars($edusharing->resource_type, ENT_COMPAT, 'utf-8') ?>" />

<!--        {#edusharing_dlg.resourceVersion} -->
    <input type="hidden" maxlength="30" size="15" name="resourceversion" id="resourceversion" />
    
       <input type="hidden" maxlength="30" size="30" name="repotype" id="repotype" />
<!--        {#edusharing_dlg.resourceid}-->
    <input type="hidden" maxlength="30" size="15" name="resource_id" id="resource_id"  value="<?php echo htmlspecialchars($edusharing->resource_id, ENT_COMPAT, 'utf-8') ?>" />
<!--        {#edusharing_dlg.ticket}-->
    <input type="hidden" maxlength="40" size="35" name="ticket" id="ticket"  value="<?php echo htmlspecialchars($ticket, ENT_COMPAT, 'utf-8') ?>" />
<div id="form_wrapper" style="float:left">
    <table>
        <tr>
            <td><?php echo htmlentities(get_string('mediasrc', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td><input disabled style="width: 150px" type="text" maxlength="300" name="object_url" id="object_url" value="<?php echo htmlspecialchars($edusharing->object_url, ENT_COMPAT, 'utf-8') ?>" ></input>
            <button type="button" name="search" value="2" onclick="window.open('<?php echo htmlspecialchars($link, ENT_COMPAT, 'utf-8'); ?>','_blank','width=1024,height=500,left=100,top=140,scrollbars=yes')">Suche</button>
            </td>
        </tr>
        <tr>
            <td><?php echo htmlentities(get_string('title', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td><input type="text" maxlength="50" style="width: 150px" name="title" id="title" value="<?php echo $title ? htmlspecialchars($edusharing->title, ENT_COMPAT, 'utf-8') : '' ?>"></input></td>
        </tr>
        <tr class="dimension">
            <td><?php echo htmlentities(get_string('window_width', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td><input type="text" maxlength="4" size="5" name="window_width" id="window_width"  value="<?php echo htmlspecialchars($edusharing->window_width, ENT_COMPAT, 'utf-8') ?>" onKeyUp="setHeight()" />&nbsp;px</td>
        </tr>
         <tr class="dimension heightProp">
            <td><?php echo htmlentities(get_string('window_height', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td><input type="text" maxlength="4" size="5" name="window_height" id="window_height" value="<?php echo htmlspecialchars($edusharing->window_height, ENT_COMPAT, 'utf-8') ?>" onKeyUp="setWidth()" />&nbsp;px</td>
        </tr>
        <tr class="dimension heightProp">
            <td></td>
            <td></td>
            <td><input type="checkbox" name="constrainProps" id="constrainProps" value="1" checked="checked"/><?php echo htmlspecialchars(get_string('constrainProportions', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
        </tr>
       
        <tr class="versionShowTr">
            <td><?php echo  htmlentities(get_string('version', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td>
                <input type="radio" value="latest" name="window_versionshow" <?php echo ($edusharing -> window_versionshow == 'latest')?'checked="checked"':''?> /><?php echo  htmlentities(get_string('versionLatest', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                <input type="radio" value="current" name="window_versionshow" <?php echo ($edusharing -> window_versionshow == 'current')?'checked="checked"':''?> /><?php echo  htmlentities(get_string('versionCurrent', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
            </td>
        </tr>
        
        <tr>
            <td><?php echo  htmlentities(get_string('float', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
            <td>:</td>
            <td>
                <input type="radio" value="left" name="window_float" <?php echo ($edusharing -> window_float == 'left')?'checked="checked"':''?> onClick="handleClick(this)" /><?php echo  htmlentities(get_string('floatLeft', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                <input type="radio" value="none" name="window_float" <?php echo ($edusharing -> window_float == 'none')?'checked="checked"':''?> onClick="handleClick(this)" /><?php echo  htmlentities(get_string('floatNone', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                <input type="radio" value="right" name="window_float" <?php echo ($edusharing -> window_float == 'right')?'checked="checked"':''?> onClick="handleClick(this)" /><?php echo  htmlentities(get_string('floatRight', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                <input type="radio" value="inline" name="window_float" <?php echo ($edusharing -> window_float == 'inline')?'checked="checked"':''?> onClick="handleClick(this)" /><?php echo  htmlentities(get_string('floatInline', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
            </td>
        </tr>
        
    </table>
    </div>
    
    <div id="preview">
        <?php echo  getPreviewText()?>
        <div id="preview_resource_wrapper"></div>
        <?php echo  getPreviewText()?>
        
    </div>
    <div style="clear:both" class="mceActionPanel">
        <input type="button" id="insert" name="insert" class="button" value="<?php echo htmlspecialchars(get_string('insert', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>" onclick="edusharingDialog.on_click_insert(this.form);" />
        <input type="button" id="cancel" name="cancel" class="button" value="<?php echo htmlspecialchars(get_string('cancel', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>" onclick="edusharingDialog.on_click_cancel();" />
    </div>

</form>

</body>

<script type="text/javascript">
    function setWidth() {
        if(!getRatioCbStatus())
            return;
        document.getElementById('window_width').value = Math.round(document.getElementById('window_height').value / getRatio());
    }
    
    function setHeight() {
        if(!getRatioCbStatus())
            return;
        document.getElementById('window_height').value = Math.round(document.getElementById('window_width').value * getRatio());
    }
    
    function getRatioCbStatus() {
        return document.getElementById('constrainProps').checked;
    }
    
    function getRatio() {
        return document.getElementById('ratio').value;
    }
    
    function refreshPreview(float) {
        style = tinymce.plugins.edusharing.getStyle(float);
        document.getElementById('preview_resource_wrapper').style = style;
    }
    
    function handleClick(radio) {
        refreshPreview(radio.value);
    }
    
    function getResourcePreview() {
        
            // splitting object-url to get object-id
        var object_url_parts = document.getElementById('object_url').value.split('/');
        var object_id = object_url_parts[3];

        var preview_url = document.getElementsByName('alfresco_base_url')[0].value + '/../../edu-sharing/preview';
        preview_url = preview_url.concat('?nodeId=' + object_id);
        preview_url = preview_url.concat('&ticket=' + document.getElementsByName('alfresco_ticket')[0].value);

        return preview_url;
    }
    
    function setPreviewContent() {
        mimeSwitchHelper = '';
        mimetype = document.getElementById('mimetype').value;
        if(mimetype.indexOf('image') !== -1)
           mimeSwitchHelper = 'image';
        else if(mimetype.indexOf('audio') !== -1)
           mimeSwitchHelper = 'audio';
        else if(mimetype.indexOf('video') !== -1)
            mimeSwitchHelper = 'video';
        else if(document.getElementById('repotype').value == 'YOUTUBE')
            mimeSwitchHelper = 'youtube';
        else
            mimeSwitchHelper = 'textlike';
        
        switch(mimeSwitchHelper) {
            case 'image': content = '<img src="'+getResourcePreview()+'" width=80/>'; break;
            case 'youtube': content = '<img src="'+getResourcePreview()+'" width=80/>'; break;
            case 'video': content = '<img src="../images/video.png" width=80/>'; break;
            case 'audio': content = '<img src="../images/audio.png" width=100/>'; break;
            default: content = '<span style="color: #00F"><?php echo getPreviewText('giveMeAShortext')?></span>'; break;
        }
        document.getElementById('preview_resource_wrapper').innerHTML = content;
        visDimensionInputs(mimeSwitchHelper);
        visVersionInputs();
    }
    
    function visVersionInputs() {
        if(document.getElementById('repotype').value == 'YOUTUBE') {
            document.getElementsByClassName('versionShowTr')[0].style.visibility = 'hidden';
        } else {
            document.getElementsByClassName('versionShowTr')[0].style.visibility = 'visible';
        }

    }
    
    function visDimensionInputs(mimeSwitchHelper) {
       
       if(mimeSwitchHelper == 'image') {
           var dimensionsSet = document.getElementsByClassName('dimension');
           for(var i = 0; i < dimensionsSet.length; i++) {
                dimensionsSet[i].style.visibility = 'visible';
            }
       } else if(mimeSwitchHelper == 'video') {
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
    
    
</script>

</html>
