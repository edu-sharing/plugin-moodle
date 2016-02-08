<?php
/*
* $McLicense$
*
* $Id$
*
*/
/*
require_once(MC_LIB_PATH.'File.class.php');
require_once(MC_LIB_PATH."MCFile.php");
require_once(MC_LIB_PATH."QTIRegister.php");
require_once(MC_LIB_PATH."User.php");
*/

class Qti2ResultService
{
	private $onyxModuleName = 'QTI2';

	private $onyxTableName = 'QTI2_RESULT';

	private $resultDirectory = 'resultfiles';


	/**
	 *
	 */
	private function getZipDest()
	{
		return MC_QTI2_PATH . $this->resultDirectory;
	}



	/**
	 *
	 */
	private function getTableName()
	{
		return $this->onyxTableName;
	}



	/**
	 *
	 */
	private function getModuleName()
	{
		return $this->onyxModuleName;
	}



	/**
	 *
	 */
	private function rmdirr($dirname)
	{
		// Sanity check
		if ( ! file_exists($dirname) )
		{
			return false;
		}

		// Simple delete for a file
		if (is_file($dirname) || is_link($dirname) )
		{
			return unlink($dirname);
		}

		// Loop through the folder
		$dir = dir($dirname);
		while (false !== ($entry = $dir->read() ) )
		{
			// Skip pointers
			if ($entry == '.' || $entry == '..')
			{
				continue;
			}

			$this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
		}
		$dir->close();

		return rmdir($dirname);
	}



	/**
	 *
	 */
	public function saveResult($identifier, $content)
	{
	    
        
        $handle = fopen('result.zip', 'w+');
        fwrite($handle, $content);
        fclose($handle);
        
        return '111';
        
		global $MC;

		if (empty($identifier) )
		{
			throw new Exception("parameter 'id' is empty");
		}

/*
@TODO : '#' is used as separator but first element (_REP_ID) may contain it too!
@TODO : _REP_ID & _APP_ID are named '_ID' but are real strings!
@TODO : _LMS_COURSE_ID, _USER_ID & _OBJECT_ID are varchars but values are int!
*/

		$arrData = explode("#", $identifier);
		if (count($arrData) != 8)
		{
			throw new Exception("invalid identifier! (6 hash tags expected)");
		}

		$repName =  trim($arrData[0]);  // _REP_ID        = name of test file e.g. 'Test1-42.zip'
		$appType =  trim($arrData[1]);  // _APP_ID        = 'lms' (static)
		#$appName =  trim($arrData[2]); //                = 'metacoon' (static)
		$roomId = intval($arrData[3]);  // _LMS_COURSE_ID = id of room where the test had been running
		$userId = intval($arrData[4]);  // _USER_ID       = id of user who had run the test
		$objId  = intval($arrData[5]);  // _OBJECT_ID     = ELQUES_REG.ELQUES_REG_ID
		#$arrData[6] are the test results original session and the file extension 'zip'

		$zipDest  = $this->getZipDest() . DIRECTORY_SEPARATOR;

		// create unique file name
		do
		{
			$resultSaveName = md5($identifier . time() . rand() );
			$resultSrc  = $zipDest . $resultSaveName . '.zip';
		}
		while (file_exists($resultSrc) );

		$resultPath = $zipDest . $resultSaveName;

		// save result into file
		$fh = fopen($resultSrc, 'w+');
		if ($fh)
		{
			fwrite($fh, $content);
			fclose($fh);
		}
		else
		{
			throw new Exception("unable to write result file '{$resultSrc}'");
		}

		// create temporary directory for extracting
		if ( ! is_dir($resultPath) )
		{
			if ( ! @mkdir($resultPath, 0777) )
			{
				throw new Exception("unable to create directory '{$resultPath}' : mkdir failed");
			}
		}

		if ( ! is_writable($resultPath) )
		{
			throw new Exception("directory '{$resultPath}' is not writeable");
		}

		// extract result file
	 	$ZIP = mc_File::factory($resultSrc);
		$ZIP->extract($resultPath . DIRECTORY_SEPARATOR);

		$QTIRegister = new QTIRegister();
		$info = $QTIRegister->getRegisteredTestData($objId);

#		$xmlResultName = "{$info['ELQUES_REG_GUID']}.xml";
		$xmlResultName = "result.xml";
		$xmlResultSrc = $resultPath . DIRECTORY_SEPARATOR . $xmlResultName;
		unset($info);

		if ( ! is_file($xmlResultSrc) )
		{
			throw new Exception("file {$xmlResultSrc} does not exist");
		}

		if ( ! is_readable($xmlResultSrc) )
		{
			throw new Exception("file {$xmlResultSrc} is not readable");
		}

		// fetch result points and delete temporary directory
		try
		{
			$DOMDocument = new DOMDocument();
			$DOMDocument->load($xmlResultSrc);

			$testResultList = $DOMDocument->getElementsByTagNameNS('*', "testResult");
			if (empty($testResultList) )
			{
				throw new Exeption("'{$xmlResultSrc}' is not a valid test result (tag 'testResult' not found)");
			}

			$OutcomeVariableList = $testResultList->item(0)->childNodes;

			$score = FALSE;
			for ($idx = 0; $idx < $OutcomeVariableList->length; $idx++)
			{
				$Outcome = $OutcomeVariableList->item($idx);
				if ($Outcome->nodeName != 'outcomeVariable')
				{
					continue;
				}

				$identifier = $Outcome->getAttribute("identifier");
				if ($identifier == 'SCORE')
				{
					$childList = $Outcome->getElementsByTagNameNS('*', "value");
					foreach($childList as $child)
					{
						if ($child->localName == 'value')
						{
							$score = $child->nodeValue;
						}
					}
				}
			}

			if ($score === FALSE)
			{
				throw new Exeption("'{$xmlResultSrc}' is not a valid test result (invalid SCORE or SCORE not found)");
			}
		}
		catch (Exception $e)
		{
			throw new Exception("unable to read test result from result file '{$xmlResultSrc}'", $e);
		}
		$this->rmdirr($resultPath);

		$User  = new User();
		$userName = $User->getUserSelectName($userId, true);
		unset($User);

		$ro = new stdClass();
		$moduleName = $this->getModuleName();
		$tabName    = $this->getTableName();

		// save result data into database
		try
		{
			$arrFields = array(
				"{$tabName}_APP_ID"        => $appType,
				"{$tabName}_ESTRACK_ID"    => '',
				"{$tabName}_REP_ID"        => $repName,
				"{$tabName}_LMS_COURSE_ID" => $roomId,
				"{$tabName}_OBJECT_ID"     => $objId,
				"{$tabName}_NAME"          => $xmlResultName,
				"{$tabName}_MODUL_ID"      => 0,
				"{$tabName}_MODUL_NAME"    => $moduleName,
				"{$tabName}_VERSION"       => '',
				"{$tabName}_USER_NAME"     => $userName,
				"{$tabName}_USER_ID"       => $userId,
				"{$tabName}_TIME"          => gmdate('Y-m-d H:i:s'),
				"{$tabName}_POINTS"        => $score,
				"{$tabName}_FILE"          => $resultSaveName,
				"COURSE_ID" => $roomId,
				"OWNER"     => $userId,
				"INSDAT"    => gmdate('Y-m-d'),
				"STATE"     => 'Y',
			);
			$MC->DB->insert($tabName, $arrFields);

			$insId = $MC->DB->getLastInsertId();

			$ro->setTrackingReturn = $insId;
		}
		catch (Exception $e)
		{
			$ro->setTrackingReturn = $e;
		}

		return $ro;
  }

}