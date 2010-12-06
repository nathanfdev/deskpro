<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Translate
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * A loader is a class that can load phrases from some resource.
 */
interface LoaderInterface
{
	/**
	 * Load all phrases from specified groups.
	 *
	 * The returned array structure should be like:
	 * <code>
	 * array('group' => array('id' => 'phrase', ...));
	 * </code>
	 *
	 * @param array $group The groups to load
	 * @return array Array of name=>phrase
	 */
	public function load($groups);
}