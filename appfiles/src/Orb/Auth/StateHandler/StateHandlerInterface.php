<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth\StateHandler;

/**
 * A state handler saves auth data between requests. This is mostly only needed
 * for adapters that require a two-step login process such as OpenID.
 */
interface StateHandlerInterface extends \ArrayAccess
{
	/**
	 * Clears all state data, or resets back into its initial state.
	 *
	 * @return void
	 */
	public function clearState();
}