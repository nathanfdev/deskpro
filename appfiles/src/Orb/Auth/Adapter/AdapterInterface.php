<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Result;

interface AdapterInterface
{
	/**
	 * Authenticate a user.
	 *
	 * @return
	 */
	public function authenticate();
}