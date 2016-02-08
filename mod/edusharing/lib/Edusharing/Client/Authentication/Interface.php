<?php

/**
 *
 *
 */
interface Edusharing_Client_Authentication_Interface
extends Edusharing_Client_Interface
{

	/**
	 *
	 * @param string $ticket
	 * @param string $username
	 */
	public function checkTicket($ticket, $username);

	/**
	 *
	 * @param string $app_id
	 */
	public function authenticateByApp($app_id);

}
