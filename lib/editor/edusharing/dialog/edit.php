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
<?php

//.oO get CC homeconf
$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$conf = $es->getHomeConf();
$propArray = $conf->prop_array;

$edusharing = new stdClass();
$edusharing -> object_url = '';
$edusharing -> course_id = $COURSE->id;
$edusharing -> id = 0;
$edusharing -> resource_type = '';
$edusharing -> resource_version = '';
$edusharing -> title = $_GET['title'];
$edusharing -> window_width = $_GET['window_width'];
$edusharing -> window_height = $_GET['window_height'];
$edusharing -> mimetype = $_GET['mimetype'];
$edusharing -> window_float = $_GET['window_float'];
$edusharing -> window_versionshow = $_GET['window_versionshow'];
$edusharing -> ratio = (int)$edusharing -> window_height / (int)$edusharing -> window_width;
$edusharing -> prev_src = $_GET['prev_src'];
$edusharing -> window_version = $_GET['window_version'];
$edusharing -> repotype = $_GET['repotype'];

function getPreviewText($short) {
    if($short)
        return 'Lorem ipsum dolor';
    return 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.';
}

$repository_id = $propArray['homerepid'];
if ( ! $repository_id )
{
    header('HTTP/1.1 500 Internal Server Error');
    throw new Exception('No home-repository configured.');
}

?>

<!--        {#edusharing_dlg.resourceVersion} -->
    <input type="hidden" maxlength="30" size="15" name="resourceversion" id="resourceversion" />
    <input type="hidden" maxlength="30" size="30" name="repotype" id="repotype" value="<?php echo $edusharing -> repotype?>"/>
    <input type="hidden" value="<?php echo $edusharing -> ratio?>" id="ratio" />
    <input type="hidden" value="<?php echo $edusharing -> window_version?>" id="window_version" />  
      
    <div id="form_wrapper" style="float:left">
        <table>
            <tr>
                <td><?php echo htmlentities(get_string('title', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
                <td>:</td>
                <td><input type="text" maxlength="50" style="width: 150px" name="title" id="title" value="<?php echo htmlspecialchars($edusharing->title, ENT_COMPAT, 'utf-8') ?>"></input></td>
            </tr>
            <tr class="dimension">
                <td><?php echo htmlentities(get_string('window_width', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
                <td>:</td>
                <td><input type="text" maxlength="4" size="5" name="window_width" id="window_width"  value="<?php echo htmlspecialchars($edusharing->window_width, ENT_COMPAT, 'utf-8') ?>" onKeyup="setHeight()" /></td>
            </tr>
             <tr class="dimension heightProp">
                <td><?php echo htmlentities(get_string('window_height', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?></td>
                <td>:</td>
                <td><input type="text" maxlength="4" size="5" name="window_height" id="window_height" value="<?php echo htmlspecialchars($edusharing->window_height, ENT_COMPAT, 'utf-8') ?>" onKeyup="setWidth()" /></td>
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
                    <input type="radio" value="left" name="window_float" <?php echo ($edusharing -> window_float == 'left')?'checked="checked"':''?> onClick="handleClick(this)"/><?php echo  htmlentities(get_string('floatLeft', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                    <input type="radio" value="none" name="window_float" <?php echo ($edusharing -> window_float == 'none')?'checked="checked"':''?> onClick="handleClick(this)"/><?php echo  htmlentities(get_string('floatNone', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                    <input type="radio" value="right" name="window_float" <?php echo ($edusharing -> window_float == 'right')?'checked="checked"':''?> onClick="handleClick(this)"/><?php echo  htmlentities(get_string('floatRight', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                    <input type="radio" value="inline" name="window_float" <?php echo ($edusharing -> window_float == 'inline')?'checked="checked"':''?> onClick="handleClick(this)"/><?php echo  htmlentities(get_string('floatInline', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>
                </td>
            </tr>
    
        </table>
    </div>
    
    <div id="preview">
        <?php echo  getPreviewText()?>
        <div id="preview_resource_wrapper"></div>
        <?php echo  getPreviewText()?>
        
    </div>

    <div style="clear: both" class="mceActionPanel">
        <input type="button" id="update" name="update" class="button" value="<?php echo htmlspecialchars(get_string('update', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>" onclick="edusharingDialog.on_click_update(document.forms[0]);" />
        <input type="button" id="cancel" name="cancel" class="button" value="<?php echo htmlspecialchars(get_string('cancel', 'editor_edusharing'), ENT_COMPAT, 'utf-8') ?>" onclick="edusharingDialog.on_click_cancel();" />
    </div>

</form>
<script type="text/javascript">

    function handleClick(radio) {
        refreshPreview(radio.value);
    }
    
    function refreshPreview(float) {
        style = tinymce.plugins.edusharing.getStyle(float);
        document.getElementById('preview_resource_wrapper').style = style;
    }

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

    function setPreviewContent() {
        
        mimeSwitchHelper = '';
        mimetype = '<?php echo $edusharing->mimetype?>';
        repotype = '<?php echo $edusharing->repotype?>';
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
            case 'image': content = '<img src="<?php echo $edusharing -> prev_src?>" width=80/>'; break;
            case 'youtube': content = '<img src="<?php echo $edusharing -> prev_src?>" width=80/>'; break;
            case 'video': content = '<img src="../images/video.png" width=80/>'; break;
            case 'audio': content = '<img src="../images/audio.png" width=80/>'; break;
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
       console.log(mimeSwitchHelper);
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
    
    refreshPreview('<?php echo $edusharing -> window_float?>');    
    setPreviewContent();
          
</script>
</body>
</html>
