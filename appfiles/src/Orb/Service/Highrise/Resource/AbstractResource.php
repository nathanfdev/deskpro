<?php
/**
 * Orb
 *
 * @package Orb
 * @subpackage Service
 * @category Highrise
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Orb\Service\Highrise\Resource;

abstract class AbstractResource
{
	/**
	 * The highrise object used to send requests
	 * @var Orb\Service\Highrise\Highrise
	 */
	protected $highrise;

	public function __construct(\Orb\Service\Highrise\Highrise $highrise)
	{
		$this->highrise = $highrise;
	}
}