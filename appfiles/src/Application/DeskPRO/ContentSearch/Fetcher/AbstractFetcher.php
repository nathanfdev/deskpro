<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\ContentSearch\Fetcher;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity\Person;

use \Orb\Util\Strings;

abstract class AbstractFetcher
{
	const TYPENAME = '__';

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @param \Application\DeskPRO\Entity\Person $person Person context to run the search from
	 */
	public function __construct(Person $person)
	{
		$this->person = $person;
	}


	
	/**
	 * Returns an array of entities identified by $related_ids, that the user is able to see.
	 * 
	 * @param array $related_ids
	 * @return array
	 */
	abstract function getEntities(array $related_ids);
}