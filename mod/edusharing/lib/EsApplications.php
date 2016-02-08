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


/**
 * handles the Applications
 *
 * @author steffen gross / matthias hupfer
 */

class EsApplications
{
	protected $conf_file;
	protected $_list;

	/**
	 *
	 */
	public function __construct($p_conf_file)
	{

    $this->conf_file = $p_conf_file;

		return true;

	} // end constructor


	/**
	 *
	 */
	final public function addFile($p_filename)
	{
     $li   = $this->getFileList();
     $li[] = $p_filename;
     $this->updateList($li);

     return true;
	}

	final public function deleteFile($p_filename)
	{
     $li   = $this->getFileList();
     if ($pos = array_search($p_filename,$li)){
        unset($li[$pos]);
     	}

     $this->updateList($li);

    		return true;
	}

	final public function updateList($p_filearray)
	{
		$app_str = implode(',',$p_filearray);

		if (file_exists($this->conf_file)) {
			$l_DOMDocument = new DOMDocument();
			$l_DOMDocument->load($this->conf_file);
		}
		$list = $l_DOMDocument->getElementsByTagName('entry');

	foreach ($list as $entry)
		{
		 if ($entry->getAttribute("key")=="applicationfiles" ){
        $entry->nodeValue = $app_str;
			  break;
		 }
    }

			$l_DOMDocument->save($this->conf_file);
    	return true;
	}


	/**
	 *
	 */
	final public function getFileList()
	{
		if ( ! file_exists($this->conf_file)) {
	      	throw new Exception ("EDU-ERROR: File not found: ".$this->conf_file);
		}

		$l_DOMDocument = new DOMDocument();
		if ( ! $l_DOMDocument->load($this->conf_file) )
		{
			throw new Exception('Error loading config-file "'.$this->conf_file.'"');
		}

		$list = $l_DOMDocument->getElementsByTagName('entry');
		foreach ($list as $entry)
		{
			if ( $entry->getAttribute("key") == "applicationfiles" )
			{
				$app_str  = $entry->nodeValue;
				break;
			}
		}

		$app_array = explode(',',$app_str);

		return $app_array;
	}

	/**
	 *
	 */
	final public function getHtmlList($path,$target)
	{

  $list   = $this->getFileList();

  $htmllist='<SELECT NAME="esappconflist" onchange="var s = this.options[this.selectedIndex].text;parent.'.$target.'.location.href=\''.$path.'?sel=\'+s">';

      $htmllist.='<option value="">-- select --</option>';


 	foreach ($list as $key => $val)
		{
      $htmllist.='<option value="'.$key.'">'.$val.'</option>';
    }

  $htmllist.='</SELECT >';

		return $htmllist;
	}



} // end class EsApplications
