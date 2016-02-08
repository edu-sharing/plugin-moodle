<?php

/**
 *
 *
 *
 */
class Edusharing_ConfigLoader_Filesystem
implements Edusharing_ConfigLoader_Interface
{

	/**
	 *
	 * @param string $ConfigDirectory where config-files are stored.
	 */
	public function __construct($ConfigDirectory)
	{
		$this->setConfigDirectory($ConfigDirectory);
	}

	/**
	 * (non-PHPdoc)
	 * @see Edusharing_ConfigLoader_Interface::loadFor()
	 */
	public function loadFor($ApplicationId)
	{
		$ConfigFilename = $this->getConfigDirectory();
		$ConfigFilename .= '/app-' . $ApplicationId . '.properties.xml';

		$Config = Edusharing_Config::fromArray($Data);

		return $Config;
	}

	/**
	 *
	 *
	 * @var string
	 */
	protected $ConfigDirectory = '';

	/**
	 *
	 *
	 * @param string $ConfigDirectory
	 * @return Edusharing_ConfigLoader_Filesystem
	 */
	public function setConfigDirectory($ConfigDirectory)
	{
		if ( ! file_exists($ConfigDirectory) ) {
			throw new Exception('Configuration-dir not found.');
		}

		$this->ConfigDirectory = $ConfigDirectory;
		return $this;
	}

	/**
	 *
	 * @return string
	 */
	public function getConfigDirectory()
	{
		return $this->ConfigDirectory;
	}

}
