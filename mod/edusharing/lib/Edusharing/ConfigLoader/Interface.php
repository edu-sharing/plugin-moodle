<?php

/**
 *
 *
 *
 */
interface Edusharing_ConfigLoader_Interface
{

	/**
	 *
	 * @param string $ApplicationId the application-id to be loaded.
	 *
	 * @return Edusharing_Config_Interface
	 */
	public function loadFor($ApplicationId);

}
