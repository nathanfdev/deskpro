<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Elastica\Searcher;

use Application\DeskPRO\Elastica\ElasticaManager;
use Application\DeskPRO\Entity\Person;

use Orb\Util\Strings;

/**
 * Searchers create queries against the elasticsearch server.
 */
abstract class AbstractSearcher
{
	/**
	 * @var \Application\DeskPRO\Elastica\ElasticaManager
	 */
	protected $manager;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Elastica\ElasticaManager $manager
	 * @var \Application\DeskPRO\Entity\Person $person
	 */
	public function __construct(ElasticaManager $manager, Person $person)
	{
		$this->manager = $manager;
		$this->person = $person;
	}

	

	/**
	 * Convert an array of document results into an array that includes their real object entities.
	 * 
	 * @param array $documents
	 * @return array
	 */
	public function documentsToResults(array $documents)
	{
		$results = array();

		foreach ($documents as $doc) {
			$type = Strings::dashToCamelCase($doc->getType());
			$class  = 'Application\\DeskPRO\\Elastica\\Type\\' . $type;

			$type = new $class($this->manager);
			$object = $type->transformToType($doc);

			if ($object) {
				$results[$object['id']] = array('entity' => $object, 'document' => $doc);
			}
		}

		return $results;
	}
}