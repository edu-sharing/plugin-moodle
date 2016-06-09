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
class mod_edusharing_render_parameter {

    var $dataArray;

    public function __construct() {
        $this->dataArray = array();
    }

    public function mod_edusharing_get_xml($p_dataarray) {
        $this->dataArray = $p_dataarray;
        return $this->mod_edusharing_make_xml();
    }

    protected function mod_edusharing_make_xml() {
        $dom = new DOMDocument('1.0');
        $root = $dom->createElement($this->dataArray[0], '');
        $dom->appendChild($root);

        foreach ($this->dataArray[1] as $key  => $value) {
            $tmp = $dom->createElement($key, '');
            $tmp_node = $root->appendChild($tmp);

            foreach ($value as $key2  => $value2) {
                $tmp2 = $dom->createElement($key2, $value2);
                $tmp_node->appendChild($tmp2);
            }
        }

        return $dom->saveXML();
    }
}

