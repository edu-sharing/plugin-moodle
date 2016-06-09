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

/**
 * @package    filter
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../mod/edusharing/lib.php');

class filter_edusharing_edurender {

    function filter_edusharing_get_render_html($url) {

        $inline = "";
        try {
            $curl_handle = curl_init($url);
            if (!$curl_handle) {
                throw new Exception('Error initializing CURL.');
            }
            curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);
            // DO NOT RETURN HTTP HEADERS
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
            // RETURN THE CONTENTS OF THE CALL
            curl_setopt($curl_handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl_handle, CURLOPT_SSL_VERIFYHOST, false);
            $inline = curl_exec($curl_handle);
            curl_close($curl_handle);

        } catch(Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            curl_close($curl_handle);
            return false;
        }

        return $inline;

    }

    function filter_edusharing_display($html) {
        global $CFG;
        error_reporting(0);
        $resId = $_GET['resId'];

        $html = str_replace(array("\n", "\r", "\n"), '', $html);
        //$html = str_replace('\'', '\\\'', $html);

        /*
         * replaces {{{LMS_INLINE_HELPER_SCRIPT}}}
         */
        $html = str_replace("{{{LMS_INLINE_HELPER_SCRIPT}}}", $CFG->wwwroot . "/filter/edusharing/inlineHelper.php?resId=" . $resId, $html);

        /*
         * replaces <es:title ...>...</es:title>
         */
        $html = preg_replace("/<es:title[^>]*>.*<\/es:title>/Uims", $_GET['title'], $html);
        /*
         * For images, audio and video show a capture underneath object
         */
        $mimetypes = array('jpg', 'jpeg', 'gif', 'png', 'bmp', 'video', 'audio');
        foreach ($mimetypes as $mimetype) {
            if (strpos($_GET['mimetype'], $mimetype) !== false)
                $html .= '<p class="caption">' . $_GET['title'] . '</p>';
        }
        echo $html;
        exit();
    }

}

$url = $_GET['URL'];

$parts = parse_url($url);
parse_str($parts['query'], $query);
require_login($query['course_id']);

$appProperties = json_decode(get_config('edusharing', 'appProperties'));
$ts = $timestamp = round(microtime(true) * 1000);
$url .= '&ts=' . $ts;
$url .= '&sig=' . urlencode(mod_edusharing_get_signature($appProperties->appid . $ts));
$url .= '&signed=' . urlencode($appProperties->appid . $ts);

$e = new filter_edusharing_edurender();
$html = $e->filter_edusharing_get_render_html($url);
$e->filter_edusharing_display($html);
