<?php

/**
 * This product Copyright 2010, 2011, 2012 metaVentis GmbH. For detailed notice,
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

require_once(dirname(__FILE__) . '/../../../config.php');

$location = $CFG->wwwroot;
if ( $_GET['course_id']) {
	$location .= '/course/view.php?id=' . intval($_GET['course_id']);
}

?>
<html>
	<head>
	</head>
	<body>
		<font face="Arial"><b>Please wait... redirecting to MOODLE LMS<span id="progressDots" style="position:absolute;"></span></b></font>
		<script type="text/javascript">
			function showProgressDots(numberOfDots, maxDots) {
				var dots = "";
				for (x=0; x<=numberOfDots; x++) {
					dots += ".";
				}
				document.getElementById('progressDots').innerHTML = dots;
				if (numberOfDots >= maxDots) {
					numberOfDots = -1;
				}
				timerHandle = setTimeout('showProgressDots('+(numberOfDots+1)+','+(maxDots)+')',200);
			}
			window.setTimeout('showProgressDots(0,8)',250);
			top.location.href = '<?php echo htmlspecialchars($location, ENT_COMPAT, 'utf-8') ?>';
		</script>
	</body>
</html>
