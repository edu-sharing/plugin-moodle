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


// @todo get original height and width + version


$eduobjres = addslashes_js(optional_param('nodeId', '', PARAM_RAW));
$eduobjtitle = addslashes_js(optional_param('title', '', PARAM_RAW));
$eduobjmimetype = addslashes_js(optional_param('mimeType', '', PARAM_RAW));
$eduobjresourcetype = addslashes_js(optional_param('resourceType', '', PARAM_RAW));
$eduobjresourceversion = addslashes_js(optional_param('resourceVersion', '', PARAM_RAW));
$eduobjheight = addslashes_js(optional_param('h', '', PARAM_RAW));
$eduobjwidth = addslashes_js(optional_param('w', '600', PARAM_RAW));
$eduobjratio = '';
if (!empty($eduobjheight) && !empty($eduobjwidth)) {
    $eduobjratio = (int)$eduobjheight / (int)$eduobjwidth;
}
$eduobjversion = addslashes_js(optional_param('v', '1.0', PARAM_RAW));
$eduobjrepotype = addslashes_js(optional_param('repoType', '', PARAM_RAW));

?>
<html>
<head>
</head>
<body>
<script type="text/javascript">
    try{
        parent.document.getElementById('object_url').value = '<?php echo $eduobjres ?>';
        parent.document.getElementById('title').value = '<?php echo $eduobjtitle ?>';
        parent.document.getElementById('mimetype').value = '<?php echo $eduobjmimetype ?>';
        parent.document.getElementById('resourcetype').value = '<?php echo $eduobjresourcetype ?>';
        parent.document.getElementById('resourceversion').value = '<?php echo $eduobjresourceversion ?>';
        parent.document.getElementById('window_height').value = '<?php echo $eduobjheight ?>';
        parent.document.getElementById('window_width').value = '<?php echo $eduobjwidth ?>';
        parent.document.getElementById('ratio').value = '<?php echo  $eduobjratio?>';
        parent.document.getElementById('window_version').value = '<?php echo  $eduobjversion?>';
        parent.document.getElementById('repotype').value = '<?php echo  $eduobjrepotype?>';
        var inputs = parent.document.getElementsByTagName("input");
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].disabled = false;
            }
    } catch (err) {
        alert('Error populating form-fields.' + err);
    }

    try {
        parent.editor_edusharing_shrink_dialog();
        parent.document.getElementById('eduframe').style.display="none";
        parent.editor_edusharing_set_preview_content();
        parent.focus();
    } catch (err) {
        alert('Error updating dialog.' + err);
    }



</script>
</body>
</html>
