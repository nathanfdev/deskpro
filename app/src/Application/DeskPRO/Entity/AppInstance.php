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

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property AppPackage $package
 * @property string $title
 * @property string $auth_key
 * @property string $secret_key
 * @property array $settings
 * @property \DateTime $date_created
 */
class AppInstance extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\AppPackage
	 */
	protected $package;

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
	 * @var array
	 */
	protected $settings = null;

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

	/**
	 * @param $name
	 * @param $value
	 */
	public function setSetting($name, $value)
	{
		$settings = $this->settings;
		$settings[$name] = $value;
		$this->setModelField('settings', $settings);
	}


	/**
	 * Get settings that we will output to JS (eg non-native only)
	 *
	 * @return array
	 */
	public function getOutputSettings()
	{
		if (!$this->settings) {
			return $this->settings;
		}

		$native_only = array();

		foreach ($this->package->settings_def as $info) {
			if (isset($info['native_only']) && $info['native_only']) {
				$native_only[$info['name']] = true;
			}
		}

		if (!$native_only) {
			return $this->settings;
		}

		$ret = $this->settings;
		foreach ($native_only as $name => $x) {
			unset($ret[$name]);
		}

		return $ret;
	}


	/**
	 * {@inheritDoc}
	 */
	public function toApiData($primary = true, $deep = true, array $visited = array())
	{
		$data = array();

		$data['id']              = $this->id;
		$data['package_name']    = $this->package->name;
		$data['title']           = $this->title;
		$data['secret_key']      = $this->secret_key;
		$data['auth_key']        = $this->auth_key;
		$data['settings']        = $this->settings ?: array();
		$data['date_created']    = $this->date_created->format('Y-m-d H:i:s');
		$data['date_created_ts'] = $this->date_created->getTimestamp();

		if ($primary) {
			$data['package'] = $this->package->toApiData(false, $deep, $visited);
		}

		return $data;
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
		$metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
		$metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AppInstance';
		$metadata->setPrimaryTable(array(
			'name' => 'app_instances'
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
			'columnName' => 'settings',
			'fieldName'  => 'settings',
			'type'       => 'json_array',
			'nullable'   => true,
		));

		$metadata->mapField(array(
			'columnName' => 'date_created',
			'fieldName'  => 'date_created',
			'type'       => 'datetime',
			'nullable'   => false,
		));

		$metadata->mapManyToOne(array(
			'fieldName'    => 'package',
			'targetEntity' => 'Application\\DeskPRO\\Entity\\AppPackage',
			'joinColumns'  => array(array(
				'name'                 => 'package_name',
				'referencedColumnName' => 'name',
				'nullable'             => true,
				'onDelete'             => 'CASCADE',
				'fetch'                => 'EAGER',
			))
		));
	}
}
