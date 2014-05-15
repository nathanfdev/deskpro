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
use Application\DeskPRO\Tickets\Triggers\TriggerActions;
use Application\DeskPRO\Tickets\Triggers\TriggerTerms;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Arrays;

/**
 * @property int $id
 * @property Department $department
 * @property EmailAccount $email_account
 * @property string $title
 * @property bool $is_enabled
 * @property string $event_trigger
 * @property array $by_agent_mode
 * @property array $by_user_mode
 * @property \Application\DeskPRO\Tickets\Triggers\TriggerTerms $terms
 * @property \Application\DeskPRO\Tickets\Triggers\TriggerActions $actions
 * @property int $run_order
 */
class TicketTrigger extends DomainObject
{
	const EVENT_TYPE_NEWTICKET                  = 'newticket';
	const EVENT_TYPE_NEWREPLY                   = 'newreply';
	const EVENT_TYPE_UPDATE                     = 'update';

	const MODE_WEB    = 'web';
	const MODE_PORTAL = 'portal';
	const MODE_WIDGET = 'widget';
	const MODE_FORM   = 'form';
	const MODE_EMAIL  = 'email';
	const MODE_API    = 'api';

	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var Department
	 */
	protected $department;

	/**
	 * @var EmailAccount
	 */
	protected $email_account;

	/**
	 * @var string
	 */
	protected $title = '';

	/**
	 * @var bool
	 */
	protected $is_enabled = true;

	/**
	 * @var bool
	 */
	protected $is_hidden = false;

	/**
	 * @var bool
	 */
	protected $is_editable = true;

	/**
	 * @var string
	 */
	protected $sys_name = null;

	/**
	 * @var string
	 */
	protected $event_trigger;

	/**
	 * @var array
	 */
	protected $by_agent_mode = array();

	/**
	 * @var array
	 */
	protected $by_user_mode = array();

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerTerms
	 */
	protected $terms;

	/**
	 * @var \Application\DeskPRO\Tickets\Triggers\TriggerActions
	 */
	protected $actions;

	/**
	 * @var int
	 */
	protected $run_order = 0;

	public function __construct()
	{
		$this->terms   = new TriggerTerms();
		$this->actions = new TriggerActions();
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param array $modes
	 */
	public function setByAgentMode($modes)
	{
		if (!$modes) {
			$this->setModelField('by_agent_mode', array());
		} else {
			if (!is_array($modes)) {
				$modes = explode(',', $modes);
			}

			$modes = Arrays::func($modes, 'trim');
			$modes = Arrays::func($modes, 'strtolower');
			sort($modes, \SORT_STRING);
			$this->setModelField('by_agent_mode', $modes);
		}
	}


	/**
	 * @param array $modes
	 */
	public function setByUserMode($modes)
	{
		if (!$modes) {
			$this->setModelField('by_user_mode', array());
		} else {
			if (!is_array($modes)) {
				$modes = explode(',', $modes);
			}

			$modes = Arrays::func($modes, 'trim');
			$modes = Arrays::func($modes, 'strtolower');
			sort($modes, \SORT_STRING);
			$this->setModelField('by_user_mode', $modes);
		}
	}


	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = parent::toApiData($primary, $deep, $visited);
		$data['department']    = $this->department ? array('id' => $this->department->id, 'title' => $this->department->title, 'title_full' => $this->department->getFullTitle()) : null;
		$data['email_account'] = $this->email_account ? array('id' => $this->email_account->id, 'address' => $this->email_account->address) : null;
		$data['by_agent_mode'] = $this->by_agent_mode;
		$data['by_user_mode']  = $this->by_user_mode;
		$data['terms']         = $this->terms->exportToArray();
		$data['actions']       = $this->actions->exportToArray();
		return $data;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketTrigger';
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_triggers'
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
			'columnName' => 'event_trigger',
			'fieldName'  => 'event_trigger',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'by_agent_mode',
			'fieldName'  => 'by_agent_mode',
			'type'       => 'simple_array',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'by_user_mode',
			'fieldName'  => 'by_user_mode',
			'type'       => 'simple_array',
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'is_enabled',
			'fieldName'  => 'is_enabled',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'is_hidden',
			'fieldName'  => 'is_hidden',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'is_editable',
			'fieldName'  => 'is_editable',
			'type'       => 'boolean',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'sys_name',
			'fieldName'  => 'sys_name',
			'type'       => 'string',
			'length'     => 150,
			'nullable'   => true,
		));
		$metadata->mapField(array(
			'columnName' => 'terms',
			'fieldName'  => 'terms',
			'type'       => 'dp_json_obj',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'actions',
			'fieldName'  => 'actions',
			'type'       => 'dp_json_obj',
			'nullable'   => false,
		));
		$metadata->mapField(array(
			'columnName' => 'run_order',
			'fieldName'  => 'run_order',
			'type'       => 'integer',
			'nullable'   => false,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'department',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\Department',
			'joinColumns'  => array(array(
				'name'                 => 'department_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'CASCADE',
			))
		));
		$metadata->mapManyToOne(array(
			'fieldName'    => 'email_account',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\EmailAccount',
			'joinColumns'  => array(array(
				'name'                 => 'email_account_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'CASCADE',
			))
		));
	}
}
