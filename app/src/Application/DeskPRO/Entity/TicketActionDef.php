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
use Application\DeskPRO\Domain\DomainObject;

/**
 * @property int $id
 * @property string $event_type
 * @property string $def_class
 * @property AppInstance $app
 * @property string $settings
 */
class TicketActionDef extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $event_type;

	/**
	 * @var string
	 */
	protected $def_class = null;

	/**
	 * @var \Application\DeskPRO\Entity\AppInstance
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $settings = null;


	/**
	 * Set settings
	 *
	 * @param array $settings
	 */
	public function setSettings(array $settings = null)
	{
		if (!$settings) {
			$this->setModelField('settings', null);
		} else {
			$this->setModelField('settings', $settings);
		}
	}


	/**
	 * Get settings
	 *
	 * @return array
	 */
	public function getSettings()
	{
		return $this->settings ? $this->settings : array();
	}


	/**
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getSetting($name, $default = null)
	{
		return isset($this->settings[$name]) ? $this->settings[$name] : $default;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketActionDef';
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

		$metadata->setPrimaryTable(array(
			'name' => 'ticket_actions_def',
			'uniqueConstraints' => array('event_type_idx' => array('columns' => array('event_type')))
		));

		$metadata->mapField(array(
			'fieldName'  => 'id',
			'columnName' => 'id',
			'type'       => 'integer',
			'id'         => true,
			'nullable'   => false,
		));

		$metadata->mapField(array(
			'fieldName'  => 'event_type',
			'columnName' => 'event_type',
			'type'       => 'string',
			'length'     => 50,
			'nullable'   => false,
		));

		$metadata->mapField(array(
			'fieldName'  => 'def_class',
			'columnName' => 'def_class',
			'type'       => 'string',
			'length'     => 255,
			'nullable'   => true,
		));

		$metadata->mapField(array(
			'fieldName'  => 'settings',
			'columnName' => 'settings',
			'type'       => 'json_array',
			'nullable'   => true,
		));
		
		$metadata->mapManyToOne(array(
			'fieldName'    => 'app',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
			'joinColumns'  => array(array(
				'name'                 => 'app_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'cascade',
			))
		));
	}
}
