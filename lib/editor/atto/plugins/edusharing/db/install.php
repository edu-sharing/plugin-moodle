<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Add the edusharing button to Atto
 *
 * @package atto_edusharing
 * @copyright metaVentis GmbH — http://metaventis.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Enable edusharing for Atto buttons on installation.
 */
function xmldb_atto_edusharing_install() {
    $toolbar = get_config('editor_atto', 'toolbar');
    if (strpos($toolbar, 'edusharing') === false && $toolbar && $toolbar != '') {
        $groups = explode("\n", $toolbar);
        // Try to put edusharing in files group.
        $found = false;
        foreach ($groups as $i => $group) {
            $parts = explode('=', $group);
            if (trim($parts[0]) == 'files') {
                $groups[$i] = 'files = ' . trim($parts[1]) . ', edusharing';
                $found = true;
            }
        }
        // Otherwise create a edusharing group in the second position starting from
        // the end.
        if (!$found) {
            do {
                $last = array_pop($groups);
            } while (empty($last) && !empty($groups));

            $groups[] = 'edusharing = edusharing';
            $groups[] = $last;
        }
        // Update config variable.
        $toolbar = implode("\n", $groups);
        set_config('toolbar', $toolbar, 'editor_atto');
    }
}
