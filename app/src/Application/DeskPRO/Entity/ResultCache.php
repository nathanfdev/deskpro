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

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set criteria
     *
     * @param array $criteria
     */
    public function setCriteria($criteria)
    {
        $this->criteria = $criteria;
    }

    /**
     * Get criteria
     *
     * @return array 
     */
    public function getCriteria()
    {
        return $this->criteria;
    }

    /**
     * Set results
     *
     * @param array $results
     */
    public function setResults($results)
    {
        $this->results = $results;
    }

    /**
     * Get results
     *
     * @return array 
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Set extra
     *
     * @param array $extra
     */
    public function setExtra($extra)
    {
        $this->extra = $extra;
    }

    /**
     * Get extra
     *
     * @return array 
     */
    public function getExtra()
    {
        return $this->extra;
    }

    /**
     * Set num_results
     *
     * @param integer $numResults
     */
    public function setNumResults($numResults)
    {
        $this->num_results = $numResults;
    }

    /**
     * Get num_results
     *
     * @return integer 
     */
    public function getNumResults()
    {
        return $this->num_results;
    }

    /**
     * Set date_created
     *
     * @param datetime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->date_created = $dateCreated;
    }

    /**
     * Get date_created
     *
     * @return datetime 
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Get person
     *
     * @return Application\DeskPRO\Entity\Person 
     */
    public function getPerson()
    {
        return $this->person;
    }


	public function getPersonId()
	{
		if (!$this->person) {
			return 0;
		}

		return $this->person['id'];
	}
}