<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package    mod
 * @subpackage edusharing
 * @copyright  metaVentis GmbH — http://metaventis.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_edusharing_render_parameter {

    private $dataarray;

    public function __construct() {
        $this->dataarray = array();
    }

    public function mod_edusharing_get_xml($pdataarray) {
        $this->dataarray = $pdataarray;
        return $this->mod_edusharing_make_xml();
    }

    protected function mod_edusharing_make_xml() {
        $dom = new DOMDocument('1.0');
        $root = $dom->createElement($this->dataarray[0], '');
        $dom->appendChild($root);

        foreach ($this->dataarray[1] as $key => $value) {
            $tmp = $dom->createElement($key, '');
            $tmpnode = $root->appendChild($tmp);

            foreach ($value as $key2 => $value2) {
                $tmp2 = $dom->createElement($key2, $value2);
                $tmpnode->appendChild($tmp2);
            }
        }

        return $dom->saveXML();
    }
}

