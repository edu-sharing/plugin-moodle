<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../mod/edusharing/lib.php');

class edurender{

  function getRenderHtml($url){

    $inline="";
        try
        {
            $curl_handle = curl_init($url);
            if ( ! $curl_handle ) {
                throw new Exception('Error initializing CURL.');
            }
            curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($curl_handle, CURLOPT_HEADER, 0);  // DO NOT RETURN HTTP HEADERS
            curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);  // RETURN THE CONTENTS OF THE CALL
            curl_setopt($curl_handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            $inline = curl_exec($curl_handle);
            curl_close($curl_handle);

        } 
        catch(Exception $e)
        {
            session_start();
            error_log( print_r($e, true) );
            curl_close($curl_handle);
            return false;
        }
        
     return $inline;
        
    }

    function display($html, $homeConf){
        global $CFG;
        error_reporting(0);
        $resId = $_GET['resId'];
        
        $html = str_replace(array("\r\n", "\r", "\n"), '', $html);      
        $html = str_replace('\'', '\\\'', $html);   

        /*
         * replaces {{{LMS_INLINE_HELPER_SCRIPT}}}
         */
        $html = str_replace("{{{LMS_INLINE_HELPER_SCRIPT}}}", $CFG->wwwroot . "/filter/edusharing/inlineHelper.php?resId=" . $resId , $html);       
                
        /*
         * replaces <es:title ...>...</es:title>
         */
        $html = preg_replace("/<es:title[^>]*>.*<\/es:title>/Uims", $_GET['title'], $html);
        /*
         * For images, audio and video show a capture underneath object
         */
         $mimetypes = array('image', 'video', 'audio');
         foreach($mimetypes as $mimetype) {
            if(strpos($_GET['mimetype'], $mimetype) !== false)
                $html .= '<p class="caption">' . $_GET['title'] . '</p>';
         }
       
        header("Content-type: text/javascript");
        header("Cache-Control: no-cache, must-revalidate");
        echo <<<blub
$( "#edu_wrapper_{$resId}" ).html('').append('{$html}').css({height: 'auto', width: 'auto'});
blub;
    }
}

$url = $_GET['URL'];
$es = new ESApp();
$es -> getApp(EDUSHARING_BASENAME);
$homeConf = $es->getHomeConf();
$appId = $homeConf -> prop_array['appid'];
$ts = $timestamp = round(microtime(true) * 1000);
$url .= '&ts=' . $ts;
$url .= '&sig=' . urlencode(getSignature($appId . $ts));
$url .= '&signed=' . urlencode($appId . $ts);

$e = new edurender();
$html = $e->getRenderHtml($url);
$e->display($html, $homeConf);

