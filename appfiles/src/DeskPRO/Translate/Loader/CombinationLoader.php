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

namespace DeskPRO\Translate\Laoder;

/**
 * Combines multiple loaders
 */
class CombinationLoader implements LoaderInterface
{
	/**
	 * @var array
	 */
	protected $loaders = array();


	public function addLoader(LoaderInterface $loader)
	{
		$this->loaders[] = $loader;
	}



	public function load($groups)
	{
		$phrases = array();

		foreach ($this->loaders as $loader) {
			try {
				$phrases = array_merge($phrases, $loader->load($groups));
			} catch (Exception $e) {}
		}

		return $phrases;
	}
}