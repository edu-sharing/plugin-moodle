<html>
<head>
</head>
<body>
<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
 *
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
    - called from ALFRESCO after selecting a node/resource in the opened popup window
    - transfers the node-id into the Location field of the opener (edit resource window)
    - closes popup
*/

require_once(dirname(__FILE__) . '/../../../../config.php');

/**
 * 
 * get original height and width + version
 *
 */

$alfresco_res = addslashes_js(optional_param('nodeId', '', PARAM_RAW));
$alfresco_title = addslashes_js(optional_param('title', '', PARAM_RAW));
$alfresco_mimetype = addslashes_js(optional_param('mimeType', '', PARAM_RAW));
$alfresco_resourceType = addslashes_js(optional_param('resourceType', '', PARAM_RAW));
$alfresco_resourceVersion = addslashes_js(optional_param('resourceVersion', '', PARAM_RAW));
$alfresco_height = addslashes_js(optional_param('h', '', PARAM_RAW));
$alfresco_width = addslashes_js(optional_param('w', '', PARAM_RAW));
$alfresco_ratio = (int)$alfresco_height / (int)$alfresco_width;
$alfresco_version = addslashes_js(optional_param('v', '1.0', PARAM_RAW));
$alfresco_repoType = addslashes_js(optional_param('repoType', '', PARAM_RAW));

?>

This page should have populated the add resource form with the url to the Repository item.<br /><br />
<a href="#" onclick="window.close();">If this window does not close on its own, please click here.</a>
<script type="text/javascript">
    try{
        opener.document.getElementById('object_url').value = '<?php echo $alfresco_res ?>';
        opener.document.getElementById('title').value = '<?php echo $alfresco_title ?>';
        opener.document.getElementById('mimetype').value = '<?php echo $alfresco_mimetype ?>';
        opener.document.getElementById('resourcetype').value = '<?php echo $alfresco_resourceType ?>';
        opener.document.getElementById('resourceversion').value = '<?php echo $alfresco_resourceVersion ?>';
        opener.document.getElementById('window_height').value = '<?php echo $alfresco_height ?>';
        opener.document.getElementById('window_width').value = '<?php echo $alfresco_width ?>';
        opener.document.getElementById('ratio').value = '<?php echo  $alfresco_ratio?>';
        opener.document.getElementById('window_version').value = '<?php echo  $alfresco_version?>';
        opener.document.getElementById('repotype').value = '<?php echo  $alfresco_repoType?>';
        opener.setPreviewContent();
        opener.focus();
    } catch(err)
    {
        alert('Error populating form-fields.');
    }

    window.close();
</script>

</body>
</html>
