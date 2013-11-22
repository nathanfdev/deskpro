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
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

class Plugin extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\PluginDef
	 */
	protected $plugin_def;

	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $auth_key;

	/**
	 * @var string
	 */
	protected $secret_key;

	/**
	 * @var \DateTime
	 */
	protected $date_created;

	public function __construct()
	{
		$this['date_created'] = new \DateTime();
		$this['secret_key']   = Strings::random(40, Strings::CHARS_ALPHANUM_IU);
		$this['auth_key']     = Strings::random(40, Strings::CHARS_ALPHANUM_IU);
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Plugin';
		$metadata->setPrimaryTable(array(
			'name' => 'plugins'
		));

		$metadata->mapField(array(
			'columnName' => 'id',
			'fieldName'  => 'id',
			'type'       => 'integer',
			'id'         => true,
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
			'columnName' => 'secret_key',
			'fieldName'  => 'secret_key',
			'type'       => 'string',
			'length'     => 40,
			'nullable'   => false,
		));

		$metadata->mapField(array(
			'columnName' => 'auth_key',
			'fieldName'  => 'auth_key',
			'type'       => 'string',
			'length'     => 40,
			'nullable'   => false,
		));

		$metadata->mapField(array(
			'columnName' => 'date_created',
			'fieldName'  => 'date_created',
			'type'       => 'datetime',
			'nullable'   => false,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'plugin_def',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\PluginDef',
			'joinColumns'  => array(array(
				'name'                 => 'plugin_def_id',
				'referencedColumnName' => 'id',
				'nullable'             => true,
				'onDelete'             => 'CASCADE',
				'fetch'                => 'EAGER',
			))
		));
	}
}
