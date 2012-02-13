<?php
/**
 * Orb
 *
 * @package Orb
 * @category Util
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Orb\Helper;

interface ShortCallableInterface
{
	/**
	 * Get an array of name=>method that will be registered on the helper.
	 * When calling helper->name(), the registered method will be called instead.
	 */
	public function getShortCallableNames();
}