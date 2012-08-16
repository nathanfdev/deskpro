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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Report builder query
 */
class ReportBuilder extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string|null
	 */
	protected $unique_key = null;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var string
	 */
	protected $description = '';

	/**
	 * @var string
	 */
	protected $query = '';

	/**
	 * @var \Application\DeskPRO\Entity\ReportBuilder
	 */
	protected $parent = null;

	/**
	 * @var bool
	 */
	protected $is_custom = true;

	/**
	 * @var string|null
	 */
	protected $category = null;

	/**
	 * List of people that have favorited this.
	 *
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $favorited_by;

	public function __construct()
	{
		$this->favorited_by = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * Gets the DPQL parts for this report's query
	 *
	 * @return array
	 */
	public function getParts()
	{
		$compiler = new \Application\DeskPRO\Dpql\Compiler();
		$statement = $compiler->compile($this->query);

		return $statement->getDpqlParts();
	}

	/**
	 * @return bool
	 */
	public function isEditable()
	{
		return ($this->is_custom || App::getConfig('debug.dev'));
	}

	/**
	 * Quick lookup handler to determine if a particular user has favorited this
	 *
	 * @var array
	 */
	protected $_is_favorited = array();

	/**
	 * Returns true if the specified person has favorited this
	 *
	 * @param Person|null $person Defaults to current person
	 *
	 * @return bool
	 */
	public function isFavorited(Person $person = null)
	{
		if ($person === null) {
			$person = App::getCurrentPerson();
		}

		$id = $person->id;

		if (!isset($this->_is_favorited[$id])) {
			$this->_is_favorited[$id] = $this->favorited_by->contains($person);
		}

		return $this->_is_favorited[$id];
	}

	/**
	 * Sets the explicit favorited status for this report for the specified person.
	 * This can be used to prevent separate lookup queries.
	 *
	 * @param Person $person
	 * @param bool $value
	 */
	public function setFavoritedStatus(Person $person, $value)
	{
		$this->_is_favorited[$person->id] = (bool)$value;
	}

	/**
	 * Adds a favorite for this report for the specified person
	 *
	 * @param Person $person
	 *
	 * @return bool
	 */
	public function addFavoritedPerson(Person $person)
	{
		$this->_is_favorited[$person->id] = true;
		return $this->favorited_by->add($person);
	}

	/**
	 * Removes the favorite status from this report for the specified person
	 *
	 * @param Person $person
	 *
	 * @return bool
	 */
	public function removeFavoritedPerson(Person $person)
	{
		$this->_is_favorited[$person->id] = false;
		return $this->favorited_by->removeElement($person);
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ReportBuilder';
		$metadata->setPrimaryTable(array(
			'name' => 'report_builder',
			'indexes' => array(
				'parent_id_idx' => array('columns' => array('parent_id'))
			),
			'uniqueConstraints' => array(
				'unique_key_idx' => array('columns' => array('unique_key'))
			)
		));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'unique_key', 'type' => 'string', 'length' => 50, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'unique_key', ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'description', 'type' => 'text', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'description', ));
		$metadata->mapField(array( 'fieldName' => 'query', 'type' => 'text', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'query', ));
		$metadata->mapField(array( 'fieldName' => 'is_custom', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'is_custom', ));
		$metadata->mapField(array( 'fieldName' => 'category', 'type' => 'string', 'length' => 25, 'precision' => 0, 'scale' => 0, 'nullable' => true, 'columnName' => 'category', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

		$metadata->mapManyToOne(array( 'fieldName' => 'parent', 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportBuilder', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'parent_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToMany(array( 'fieldName' => 'favorited_by', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'indexBy' => 'id', 'joinTable' => array( 'name' => 'report_builder_favorite', 'schema' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'report_builder_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), 'inverseJoinColumns' => array( 0 => array( 'name' => 'person_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ), ), ));
	}
}
