<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Mapping\ClassMetadata as ValidatorClassMetadata;

/**
 * An agent team is a group of agents. Similar to usergroups but for agents.
 *
 */
class AgentTeam extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 */
	protected $id = null;


	/**
	 * @var string
	 */
	protected $name;


	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 */
	protected $members = null;

	/**
	 * @var Blob
	 */
	protected $avatar;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function __construct()
	{
		$this->members = new ArrayCollection();
	}


	public function addPerson(Entity\Person $person)
	{
		if ($this->members->contains($person)) {
			return;
		}
		$this->members->add($person);
		$this->_onPropertyChanged('members', $this->members, $this->members);
	}

	public function removePerson(Entity\Person $person)
	{
		$this->members->removeElement($person);
		$this->_onPropertyChanged('members', $this->members, $this->members);
	}

	public function hasAvatar()
	{
		return $this->avatar && $this->avatar->isImage();
	}

	public function getAvatarUrl($size = 50)
	{
		if (!$this->hasAvatar()) return null;
		return $this->avatar->getThumbnailUrl($size);
	}


	############################################################################
	# Validation Metadata
	############################################################################

	public static function loadValidatorMetadata(ValidatorClassMetadata $metadata)
	{
		$metadata->addPropertyConstraint('name', new NotBlank());
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentTeam';
		$metadata->setPrimaryTable(array( 'name' => 'agent_teams', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'name', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'name', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToMany(array(
			'fieldName' => 'members',
			'mapedBy' => 'teams',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
			'joinTable' => array(
				'name' => 'agent_team_members',
				'joinColumns' => array(array( 'name' => 'team_id' )),
				'inverseJoinColumns' => array(array( 'name' => 'person_id' )),
				'onDelete' => 'cascade',
			),
			'orderBy' => array( 'name' => 'ASC', ),
		));
		$metadata->mapManyToOne(array(
			'fieldName' => 'avatar',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Blob',
			'mappedBy' => NULL,
			'inversedBy' => NULL,
			'joinColumns' => array(array(
				'name' => 'avatar_blob_id',
				'referencedColumnName' => 'id',
				'nullable' => true,
				'onDelete' => 'set null',
			)),
			'dpApi' => true
		));
	}
}
