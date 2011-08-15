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

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A result cache is a cached result from a search or filter.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="result_cache")
 */
class ResultCache extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 * 
	 */
	protected $id = null;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", inversedBy="preferences")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person;

	/**
	 * Criteria information like what the user searched for
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="criteria", type="array")
	 */
	protected $criteria = array();

	/**
	 * An array of results
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="results", type="array")
	 */
	protected $results = array();

	/**
	 * Any extra data
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="extra", type="array")
	 */
	protected $extra = array();

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="num_results", type="integer")
	 */
	protected $num_results = 0;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function setPerson($person)
	{
		$this->person = $person;
		$this->person_id = $person['id'];
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}


	
	/**
	 * Get some data from the extra array
	 */
	public function getExtraData($key, $default = null)
	{
		return (isset($this->extra[$key]) ? $this->extra[$key] : $default);
	}


	/**
	 * Set some data on the extra array.
	 *
	 * @param  $key
	 * @param  $value
	 * @return void
	 */
	public function setExtraData($key, $value)
	{
		if ($value === null) {
			unset($this->extra[$key]);
		} else {
			$this->extra[$key] = $value;
		}
	}
}