<?php

// create preview link with signature
require_once dirname(__FILE__) . '/../../../config.php';
require_once dirname(__FILE__) . '/../../../mod/edusharing/locallib.php';

global $DB;

$resourceId = $_GET['resourceId'];

if (!$edusharing = $DB->get_record(EDUSHARING_TABLE, array('id' => $resourceId))) {
    throw new Exception('Error loading edusharing-object from database.');
}

$courseId = $edusharing -> course;

$es = new ESApp();
$app = $es->getApp(EDUSHARING_BASENAME);
$homeConf = $es->getHomeConf();
$appId = $homeConf -> prop_array['appid'];

$previewService = str_replace('services', 'preview', $homeConf -> prop_array['cc_webservice_url']);

$objectUrl = urldecode($_GET['objectUrl']);
$objectUrlParts = str_replace('ccrep://', '', $objectUrl);
$objectUrlParts = explode('/', $objectUrlParts);

$repoId = $objectUrlParts[0];
$nodeId = $objectUrlParts[1];

$time = round(microtime(true) * 1000);

$url = $previewService;
$url .= '?appId=' . $appId;
$url .= '&courseId=' . $courseId;
$url .= '&repoId=' . $repoId;
$url .= '&nodeId=' . $nodeId;
$url .= '&resourceId=' . $resourceId;
$url .= '&version=' . $edusharing -> object_version;

$sig = urlencode(getSignature($appId . $time));

$url .= '&sig=' . $sig;
$url .= '&ts=' . $time;

$curl_handle = curl_init($url); 
curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($curl_handle, CURLOPT_HEADER, 0);
curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl_handle, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
$output = curl_exec($curl_handle); 

curl_close($curl_handle);      
echo $output;
exit();
