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
 * @package    editor
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');

/**
 * 
 * @todo get original height and width + version
 *
 */

$eduObj_res = addslashes_js(optional_param('nodeId', '', PARAM_RAW));
$eduObj_title = addslashes_js(optional_param('title', '', PARAM_RAW));
$eduObj_mimetype = addslashes_js(optional_param('mimeType', '', PARAM_RAW));
$eduObj_resourceType = addslashes_js(optional_param('resourceType', '', PARAM_RAW));
$eduObj_resourceVersion = addslashes_js(optional_param('resourceVersion', '', PARAM_RAW));
$eduObj_height = addslashes_js(optional_param('h', '', PARAM_RAW));
$eduObj_width = addslashes_js(optional_param('w', '600', PARAM_RAW));
$eduObj_ratio = '';
if(!empty($eduObj_height) && !empty($eduObj_width))
    $eduObj_ratio = (int)$eduObj_height / (int)$eduObj_width;
$eduObj_version = addslashes_js(optional_param('v', '1.0', PARAM_RAW));
$eduObj_repoType = addslashes_js(optional_param('repoType', '', PARAM_RAW));

?>
<html>
<head>
</head>
<body>
<script type="text/javascript">
    try{
        parent.document.getElementById('object_url').value = '<?php echo $eduObj_res ?>';
        parent.document.getElementById('title').value = '<?php echo $eduObj_title ?>';
        parent.document.getElementById('mimetype').value = '<?php echo $eduObj_mimetype ?>';
        parent.document.getElementById('resourcetype').value = '<?php echo $eduObj_resourceType ?>';
        parent.document.getElementById('resourceversion').value = '<?php echo $eduObj_resourceVersion ?>';
        parent.document.getElementById('window_height').value = '<?php echo $eduObj_height ?>';
        parent.document.getElementById('window_width').value = '<?php echo $eduObj_width ?>';
        parent.document.getElementById('ratio').value = '<?php echo  $eduObj_ratio?>';
        parent.document.getElementById('window_version').value = '<?php echo  $eduObj_version?>';
        parent.document.getElementById('repotype').value = '<?php echo  $eduObj_repoType?>';
        var inputs = parent.document.getElementsByTagName("input");
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].disabled = false;
            }
    } catch(err) {
        alert('Error populating form-fields.' + err);
    }
    
    try {
        parent.editor_edusharing_shrink_dialog();
        parent.document.getElementById('eduframe').style.display="none";
        parent.editor_edusharing_set_preview_content();
        parent.focus();
    } catch(err) {
        alert('Error updating dialog.' + err);
    }
    
    

</script>
</body>
</html>
