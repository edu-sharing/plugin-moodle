<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../mod/edusharing/lib.php');
$url = $_REQUEST['url'];
$es = new ESApp();
$es -> getApp(EDUSHARING_BASENAME);
$homeConf = $es->getHomeConf();
$appId = $homeConf->prop_array['appid'];
$ts = $timestamp = round(microtime(true) * 1000);
$url .= '&ts=' . $ts;
$url .= '&sig=' . rawurlencode(getSignature($appId . $ts));
header("Location: " . $url);
exit(0);
