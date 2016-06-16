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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Define capabilities
 *
 * @package    block_edusharing_search
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$capabilities = array(

    'block/edusharing_search:myaddinstance'  => array(
        'captype'  => 'write',
        'contextlevel'  => CONTEXT_SYSTEM,
        'archetypes'  => array(
            'user'  => CAP_ALLOW
        ),

        'clonepermissionsfrom'  => 'moodle/my:manageblocks'
    ),

    'block/edusharing_search:addinstance'  => array(
        'riskbitmask'  => RISK_SPAM | RISK_XSS,
        'captype'  => 'write',
        'contextlevel'  => CONTEXT_BLOCK,
        'archetypes'  => array(
            'editingteacher'  => CAP_ALLOW,
            'manager'  => CAP_ALLOW
        ),
        'clonepermissionsfrom'  => 'moodle/site:manageblocks'
    ),
);