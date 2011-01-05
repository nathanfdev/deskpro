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

namespace Application\DeskPRO\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A result cache is a cached result from a search or filter.
 *
 * @orm:Entity
 * @orm:Table(name="result_cache")
 */
class ResultCache extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The person ID
	 *
	 * @var int
	 * @orm:Column(name="person_id", type="integer")
	 */
	protected $person_id;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @orm:Id
	 * @orm:ManyToOne(targetEntity="Person", inversedBy="preferences")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person;

	/**
	 * Criteria information like what the user searched for
	 *
	 * @var array
	 * @orm:Column(name="criteria", type="array")
	 */
	protected $criteria = array();

	/**
	 * An array of results
	 *
	 * @var array
	 * @orm:Column(name="results", type="array")
	 */
	protected $results = array();

	/**
	 * @var int
	 * @orm:Column(name="num_results", type="integer")
	 */
	protected $num_results = 0;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}