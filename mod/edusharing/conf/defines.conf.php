<?php

/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
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

# ERROR HANDLING
define('MC_DIE_ON_ERROR', false);
define('MC_DEBUG',   $DEBUG);
define('MC_DEVMODE', $DEVMODE);


# HTML DEFAULT CHARACTER SET
define('MC_CHAR_SET', 'utf-8');

# INTERNAL PATH DEFINITIONS
define("MC_SCHEME", $MC_SCHEME);
define("MC_HOST",   $MC_HOST);
define("MC_PATH",   $MC_PATH);
define("MC_DOCROOT",$MC_DOCROOT);
define("MC_ROOT_PATH", MC_DOCROOT.MC_PATH);
define("MC_ROOT_URI",  MC_SCHEME.'://'.MC_HOST.MC_PATH);
define("MC_EMBED_PATH",   MC_ROOT_PATH."func/embed/");
define("MC_LANG_PATH",    MC_ROOT_PATH."lang/");
define("MC_LIB_PATH",     MC_ROOT_PATH."func/classes.new/");

define("CC_CONF_PATH",     MC_ROOT_PATH."conf/");	// 
define("CC_CONF_APPFILE",  "ccapp-registry.properties.xml");	// 
define("CC_LOCALE_PATH",   MC_ROOT_PATH."locale/");	// 
define("CC_LOCALE_FILE",  "lang.common.php");	// 


define("LIB_NEW_FULLPATH",  MC_LIB_PATH);	// deprecated parameter



/*** END of DEFINITION block ***/

// declaring include paths
array_unshift($MC_INCLUDE_PATH, '.');
ini_set("include_path", implode(':', array_unique($MC_INCLUDE_PATH)));
unset($MC_INCLUDE_PATH);
