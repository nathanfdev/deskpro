<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A result cache is a cached result from a search or filter.
 *
 */
class ResultCache extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 *
	 */
	protected $id = null;

	/**
	 * @var Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * Criteria information like what the user searched for
	 *
	 * @var array
	 */
	protected $criteria = array();

	/**
	 * An array of results
	 *
	 * @var array
	 */
	protected $results = array();

	/**
	 * Any extra data
	 *
	 * @var array
	 */
	protected $extra = array();

	/**
	 * @var int
	 */
	protected $num_results = 0;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	public function setPerson($person)
	{
		$this['person'] = $person;
	}

	public function __construct()
	{
		$this['date_created'] = new \DateTime();
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
        $this['criteria'] = $criteria;
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
        $this['results'] = $results;
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
        $this['extra'] = $extra;
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
        $this['num_results'] = $numResults;
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
        $this['date_created'] = $dateCreated;
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



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(array( 'name' => 'result_cache', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'criteria', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'criteria', ));
		$metadata->mapField(array( 'fieldName' => 'results', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'results', ));
		$metadata->mapField(array( 'fieldName' => 'extra', 'type' => 'array', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'extra', ));
		$metadata->mapField(array( 'fieldName' => 'num_results', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'num_results', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'person', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => 'preferences', 'joinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
	}
}
