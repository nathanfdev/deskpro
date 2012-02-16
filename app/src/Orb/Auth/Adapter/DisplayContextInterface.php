<?php
/**
 * Orb
 *
 * @package Orb
 * @category Auth
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Auth\Adapter;

/**
 * Adapters that behave differently depending on how they are used can
 * implmenet this interface.
 */
interface DisplayContextInterface extends AdapterInterface
{
	/**
	 * @param mixed $context Context information
	 */
	public function setDisplayContext($context);
}
