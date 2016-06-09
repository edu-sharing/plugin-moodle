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
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
error_reporting(E_ERROR);

$appProperties = json_decode(get_config('edusharing', 'appProperties'));

$parsedWwwroot = parse_url($CFG->wwwroot);
if ($_GET['wsScheme'] == 'http' || $_GET['wsScheme'] == 'https')
    $parsedWwwroot['scheme'] = $_GET['wsScheme'];
if (isset($_GET['wsForceIpAddress']))
    $parsedWwwroot['host'] = $appProperties->host;
$wsBaseUrl = $parsedWwwroot['scheme'] . '://' . $parsedWwwroot['host'];
if (!empty($appProperties->port))
    $wsBaseUrl .= ':' . $hc->prop_array['port'];
$wsBaseUrl .= $parsedWwwroot['path'];

if (empty($appProperties->signatureRedirector)) {
    require_once(dirname(__FILE__) . '/mod_edusharing_app_property_helper.php');
    $mod_edusharing_app_property_helper = new mod_edusharing_app_property_helper($hc);
    $mod_edusharing_app_property_helper->mod_edusharing_add_signature_redirector();
    $appProperties = json_decode(get_config('edusharing', 'appProperties'));
}

if (empty($appProperties->public_key)) {
    require_once(dirname(__FILE__) . '/mod_edusharing_app_property_helper.php');
    $mod_edusharing_app_property_helper = new mod_edusharing_app_property_helper($hc);
    $mod_edusharing_app_property_helper->mod_edusharing_add_ssl_keypair_to_home_config();
    $appProperties = json_decode(get_config('edusharing', 'appProperties'));
}


$xml = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8" ?><!DOCTYPE properties SYSTEM "http://java.sun.com/dtd/properties.dtd"><properties></properties>');

$entry = $xml->addChild('entry', $appProperties->appid);
$entry->addAttribute('key', 'appid');
$entry = $xml->addChild('entry', $appProperties->type);
$entry->addAttribute('key', 'type');
$entry = $xml->addChild('entry', 'moodle');
$entry->addAttribute('key', 'subtype');
$entry = $xml->addChild('entry', parse_url($CFG->wwwroot, PHP_URL_HOST));
$entry->addAttribute('key', 'domain');
$entry = $xml->addChild('entry', $appProperties->host);
$entry->addAttribute('key', 'host');
$entry = $xml->addChild('entry', 'true');
$entry->addAttribute('key', 'trustedclient');
$entry = $xml->addChild('entry', 'moodle:course/update');
$entry->addAttribute('key', 'hasTeachingPermission');
$entry = $xml->addChild('entry', $appProperties->public_key);
$entry->addAttribute('key', 'public_key');
$entry = $xml->addChild('entry', $appProperties->signatureRedirector);
$entry->addAttribute('key', 'signatureRedirector');

header('Content-type: text/xml');
print($xml->asXML());
exit();
