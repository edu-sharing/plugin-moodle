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

/**
 * Providing a block to link directly to an user's workspace on repository.
 *
 * @package   mod
 * @subpackage edusharing
 * @copyright 2010 metaVentis GmbH
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_edusharing_workspace extends block_base {

	public function init() {
		$this->title   = get_string('block_title', 'block_edusharing_workspace');
		$this->version = 2015060901;
	}

	/**
	 * (non-PHPdoc)
	 * @see blocks/block_base::get_content()
	 */
    public function get_content()
    {
		if ($this->content !== NULL)
		{
			return $this->content;
		}

		global $CFG;
		global $COURSE;

		$this->content =  new stdClass;
		$this->content->text = '<form action="'.$CFG->wwwroot.'/blocks/edusharing_workspace/helper/cc_workspace.php" method="get"><input type="hidden" name="id" value="'.$COURSE->id.'" /><input type="submit" value="'.htmlentities(get_string('button_text', 'block_edusharing_workspace')).'" /></form>';

		return $this->content;
	}

}
