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
 * Popuphelper script for repo
 *
 * Called to upload/select edu-sharing object.
 * Intrduces an iframe to retain window.opener
 *
 * @package    mod_edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
$url = required_param('rurl', PARAM_NOTAGS);
require_login();
require_sesskey();
?>

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <script>
            function setNode(id, title) {
                window.opener.document.getElementById('id_object_url').value = id;
                if(window.opener.document.getElementById('id_name').value === '')
                    window.opener.document.getElementById('id_name').value = title;
                window.opener.focus();
                window.close();
            }
        </script>
    </head>
    <body style="padding:0;margin:0;">
        <iframe src="<?php echo $url ?>" style="height:100%;width:100%;border:0;"></iframe>
    </body>
</html>
