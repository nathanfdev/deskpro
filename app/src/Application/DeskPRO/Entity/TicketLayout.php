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

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * @property int $id
 * @property Department $department
 * @property bool $is_enabled
 * @property \Application\DeskPRO\TicketLayout\Layout $user_layout
 * @property \Application\DeskPRO\TicketLayout\Layout $agent_layout
 * @property \DateTime $date_updated
 */
class TicketLayout extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var Department
	 */
	protected $department;

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var \Application\DeskPRO\TicketLayout\Layout
	 */
	protected $user_layout;

	/**
	 * @var \Application\DeskPRO\TicketLayout\Layout
	 */
	protected $agent_layout;

	/**
	 * @var \DateTime
	 */
	protected $date_updated;

	public function __construct(Department $department = null)
	{
		$this->department   = $department;
		$this->user_layout  = new Layout();
		$this->agent_layout = new Layout();
		$this->date_updated = new \DateTime();
	}


	/**
	 * @throws \RuntimeException
	 */
	public function setDepartment()
	{
		throw new \RuntimeException("Operation not supported");
	}


	/**
	 * Enable the layout
	 */
	public function enable()
	{
		$this['is_enabled'] = true;
	}


	/**
	 * Disable the layout
	 */
	public function disable()
	{
		$this['is_enabled'] = true;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_layouts'
		));

		$metadata->mapField(array(
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'nullable'   => false,
			'id'         => true
		));
		$metadata->mapField(array(
			'columnName' => 'is_enabled',
			'fieldName'  => 'is_enabled',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'user_layout',
			'fieldName'  => 'user_layout',
			'type'       => 'dp_json_obj',
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'agent_layout',
			'fieldName'  => 'agent_layout',
			'type'       => 'dp_json_obj',
			'nullable'   => false
		));
		$metadata->mapField(array(
			'columnName' => 'date_updated',
			'fieldName'  => 'date_updated',
			'type'       => 'datetime',
			'nullable'   => false
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'department',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
			'joinColumns'  => array(array(
				'name'                 => 'department_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
			))
		));
	}
}