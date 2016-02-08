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

/*
 * defaults.php - lets you easily define default values for your configuration 
 * variables. It is included by upgrade_activity_modules in lib/adminlib.php. 
 * It should define an array $defaults. These values are then loaded into the
 * config table. Alternatively, if you set $defaults['_use_config_plugins'] to
 * true, the values are instead loaded into the config_plugins table, which is 
 * better practice. 
 *
 * See mod/quiz/defaults.php for an example.
 * (This apparently only works with moodle 2.x branch.)
 *
 */

$defaults = array(
	'_use_config_plugins' => true,
	'nodeName' => '',
	
);

