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
use Application\DeskPRO\Tickets\Macros\MacroActions;

/**
 * Ticket macros
 */
class TicketMacro extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person = null;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 */
	protected $is_global = false;

	/**
	 * @var \Application\DeskPRO\Tickets\Macros\MacroActions
	 */
	protected $actions = array();

	public function __construct()
	{
		$this->actions = new MacroActions();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['person_id'] = $this->person ? $this->person->getId() : null;
		$data['person']    = $this->person ? $this->person->toApiData(false, $deep, $visited) : null;
		$data['actions']   = $this->actions->exportToArray();
		return $data;
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketMacro';
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_macros',
		));

		$metadata->mapField(array(
			'id'         => true,
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'title',
			'fieldName'  => 'title',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'is_enabled',
			'fieldName'  => 'is_enabled',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'is_global',
			'fieldName'  => 'is_global',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'fieldName'  => 'actions',
			'type'       => 'object',
			'nullable'   => false,
			'columnName' => 'actions',
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'person',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
			'dpApi'        => true,
			'joinColumns'  => array(array(
				'name'                 => 'person_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'set null',
			))
		));
	}
}
