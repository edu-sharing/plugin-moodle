<?php
/**
 * This product Copyright 2010 metaVentis GmbH.  For detailed notice,
 * see the "NOTICE" file with this distribution.
 * 
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
	creates a XML file from given array...
	note: only 3 levels working/needed yet:  root/block/element&value

	example:
						$data4xml = array("ccrender");

						$data4xml[1]["ccuser"]["id"] = 'user@host.org';
						$data4xml[1]["ccuser"]["name"] = "FirstName LastName";

						$data4xml[1]["ccserver"]["ip"] = '127.0.0.1';
						$data4xml[1]["ccserver"]["hostname"] = 'www.host.org';
						$data4xml[1]["ccserver"]["mnet_localhost_id"] = '007-xxx-0815';
						...

						$XMLmaker = new RenderParameter();
						$xml = $XMLmaker->getXML($data4xml);

						//==== == =
							header('Content-type: text/xml');
							echo($xml);
							die;
						//==== == =
*/

class RenderParameter {

	var $dataArray;

	public function __construct() {
		$this->dataArray = array();
	}

	public function getXML($p_dataarray) {
		$this->dataArray = $p_dataarray;
		return $this->makeXML();
	}

	protected function makeXML() {
		$dom = new DOMDocument('1.0');
		$root = $dom->createElement($this->dataArray[0], '');
		$dom->appendChild($root);

		foreach ($this->dataArray[1] as $key => $value) {
			$tmp = $dom->createElement($key, '');
			$tmp_node = $root->appendChild($tmp);

			foreach ($value as $key2 => $value2) {
				$tmp2 = $dom->createElement($key2, $value2);
				$tmp_node->appendChild($tmp2);
			}
		}

		return $dom->saveXML();
	}
}

