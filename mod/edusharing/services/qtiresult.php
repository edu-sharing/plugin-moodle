<?php
/*
* $McLicense$
*
* $Id$
*
*/

//require_once(dirname(__FILE__) . '/../../dblog.inc.php');
//require_once(dirname(__FILE__) . '/conf/qti2.conf.php');
//require_once(MC_QTI2_LIB_PATH . "Qti2ResultService.php");
require_once(dirname(__FILE__).'/../qti/Qti2ResultService.php');
$wsdl_write = true;
//require_once(MC_QTI2_PATH . "qtiresult.wsdl.php"); // skipping problem with url_fopen

libxml_disable_entity_loader(false);
$SoapServer = new SoapServer("qtiresult.wsdl");
$SoapServer->setClass("Qti2ResultService");
$SoapServer->handle();

