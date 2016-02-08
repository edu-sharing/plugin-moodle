<?php

require_once (dirname(__FILE__) . '/../../config.php');
define('CC_CONF_PATH', dirname(__FILE__) . DIRECTORY_SEPARATOR);
define('CC_CONF_APPFILE', 'ccapp-registry.properties.xml');

require_once ($CFG -> dirroot . '/mod/edusharing/lib/ESApp.php');
require_once ($CFG -> dirroot . '/mod/edusharing/lib/EsApplications.php');
require_once ($CFG -> dirroot . '/mod/edusharing/lib/EsApplication.php');

function getEntryElement($dom, $key, $val) {

    $entry = $dom -> createElement('entry', $val);
    $entry -> setAttribute("key", $key);
    return $entry;
};

$impl = new DOMImplementation();
$dtd = $impl -> createDocumentType('properties', '', 'http://java.sun.com/dtd/properties.dtd');

$dom = $impl -> createDocument('1.0', '', $dtd);
$dom -> encoding = 'UTF-8';
$dom -> preserveWhiteSpace = false;
$dom -> formatOutput = true;
$element = $dom -> createElement('properties');
$dom -> appendChild($element);

if (empty($_SERVER['HTTPS'])) {
    $prot = 'http://';
} else {
    $prot = 'https://';
};

$application = new ESApp();
$application -> getApp('conf/esmain');
$hc = $application -> getHomeConf();

$parsedWwwroot = parse_url($CFG -> wwwroot);
if ($_GET['wsScheme'] == 'http' || $_GET['wsScheme'] == 'https')
    $parsedWwwroot['scheme'] = $_GET['wsScheme'];
if (isset($_GET['wsForceIpAddress']))
    $parsedWwwroot['host'] = $hc -> prop_array['host'];
$wsBaseUrl = $parsedWwwroot['scheme'];
$wsBaseUrl .= '://';
$wsBaseUrl .= $parsedWwwroot['host'];
if (!empty($hc -> prop_array['port']))
    $wsBaseUrl .= ':' . $hc -> prop_array['port'];
$wsBaseUrl .= $parsedWwwroot['path'];

if (empty($hc -> prop_array['public_key'])) {
    require_once (dirname(__FILE__) . '/AppPropertyHelper.php');
    $appPropertyHelper = new AppPropertyHelper($hc);
    $appPropertyHelper -> addSslKeypairToHomeConfig();
    $application = new ESApp();
    $application -> getApp('conf/esmain');
    $hc = $application -> getHomeConf();
}

if (empty($hc -> prop_array['signatureRedirector'])) {
    require_once (dirname(__FILE__) . '/AppPropertyHelper.php');
    $appPropertyHelper = new AppPropertyHelper($hc);
    $appPropertyHelper -> addSignatureRedirector();
    $application = new ESApp();
    $application -> getApp('conf/esmain');
    $hc = $application -> getHomeConf();
}

// test param USERNAME
$auth_by_app_username_prop = 'USERNAME';
if (!empty($_GET['auth_by_app_username_prop'])) {
    $auth_by_app_username_prop = $_GET['auth_by_app_username_prop'];
}

// test param auth_by_app_sendmail
$auth_by_app_sendmail = 'false';
if (!empty($_GET['auth_by_app_sendmail'])) {
    $auth_by_app_sendmail = 'true';
}

// test param blowfishkey
$blowfishkey = 'thetestkey';
if (!empty($hc -> prop_array['blowfishkey'])) {
    $blowfishkey = $hc -> prop_array['blowfishkey'];
} else if (!empty($_GET['blowfishkey'])) {
    $blowfishkey = $_GET['blowfishkey'];
}

// test param blowfishiv
$blowfishiv = 'initvect';
if (!empty($hc -> prop_array['blowfishiv'])) {
    $blowfishiv = $hc -> prop_array['blowfishiv'];
} else if (!empty($_GET['blowfishiv'])) {
    $blowfishiv = $_GET['blowfishiv'];
}

$entry = getEntryElement($dom, 'appid', $hc -> prop_array['appid']);
$element -> appendChild($entry);

if (!empty($hc -> prop_array['appcaption'])) {
    $entry = getEntryElement($dom, 'appcaption', $hc -> prop_array['appcaption']);
    $element -> appendChild($entry);
}

$entry = getEntryElement($dom, 'type', $hc -> prop_array['type']);
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'subtype', 'moodle');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'domain', parse_url($CFG -> wwwroot, PHP_URL_HOST));
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'host', $hc -> prop_array['host']);
$element -> appendChild($entry);

if (!empty($hc -> prop_array['port'])) {
    $entry = getEntryElement($dom, 'port', $hc -> prop_array['port']);
    $element -> appendChild($entry);
}

if (empty($hc -> prop_array['trustedclient'])) {
    $hc -> prop_array['trustedclient'] = 'true';
}

$entry = getEntryElement($dom, 'trustedclient', $hc -> prop_array['trustedclient']);
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'authenticationwebservice', $wsBaseUrl . '/mod/edusharing/services/authentication.php');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'authenticationwebservice_wsdl', $wsBaseUrl . '/mod/edusharing/services/authentication.php?wsdl');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'permissionwebservice', $wsBaseUrl . '/mod/edusharing/services/permission.php');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'permissionwebservice_wsdl', $wsBaseUrl . '/mod/edusharing/services/permission.php?wsdl');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'hasTeachingPermission', 'moodle:course/update');
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'blowfishkey', $blowfishkey);
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'blowfishiv', $blowfishiv);
$element -> appendChild($entry);
/*
$entry = getEntryElement($dom, 'auth_by_app_username_prop', $auth_by_app_username_prop);
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'auth_by_app_sendmail', $auth_by_app_sendmail);
$element -> appendChild($entry);
*/
$entry = getEntryElement($dom, 'public_key', $hc -> prop_array['public_key']);
$element -> appendChild($entry);

$entry = getEntryElement($dom, 'signatureRedirector', $hc -> prop_array['signatureRedirector']);
$element -> appendChild($entry);

header("Content-Type: application/xhtml+xml; charset=utf-8");
print $dom -> saveXML();
