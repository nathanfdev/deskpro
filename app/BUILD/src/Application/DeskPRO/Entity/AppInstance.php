<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property AppPackage $package
 * @property string $perm_type
 * @property string $title
 * @property string $auth_key
 * @property string $secret_key
 * @property array $settings
 * @property \DateTime $date_created
 */
class AppInstance extends DomainObject
{
    const PERM_TYPE_GLOBAL = 'set';
    const PERM_TYPE_SET    = 'global';

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
    protected $perm_type = 'global';

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
        $this['secret_key']   = DpStrings::random(40, Strings::CHARS_ALPHANUM_IU);
        $this['auth_key']     = DpStrings::random(40, Strings::CHARS_ALPHANUM_IU);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set settings.
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
     * Get settings.
     *
     * @return array
     */
    public function getSettings()
    {
        return $this->settings ? $this->settings : [];
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
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
        $settings        = $this->settings;
        $settings[$name] = $value;
        $this->setModelField('settings', $settings);
    }

    /**
     * Get settings that we will output to JS (eg non-native only).
     *
     * @return array
     */
    public function getOutputSettings()
    {
        if (!$this->settings) {
            return $this->settings;
        }

        $native_only = [];

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
     * @param string $type Event type (update, newticket, newreply)
     *
     * @return array
     */
    public function getTriggerEvents($type)
    {
        $trigger_events = $this->package->trigger_events;
        if (!$trigger_events || empty($trigger_events[$type])) {
            return [];
        }

        $events = [];

        foreach ($trigger_events[$type] as $name => $label) {
            $events[] = [
                'event_id' => $this->package->name.'.'.$this->id.'.'.$name,
                'name'     => $name,
                'label'    => $label,
            ];
        }

        return $events;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data = [];

        $data['id']              = $this->id;
        $data['package_name']    = $this->package->name;
        $data['title']           = $this->title;
        $data['perm_type']       = $this->perm_type;
        $data['secret_key']      = $this->secret_key;
        $data['auth_key']        = $this->auth_key;
        $data['settings']        = $this->settings ?: [];
        $data['date_created']    = $this->date_created->format('Y-m-d H:i:s');
        $data['date_created_ts'] = $this->date_created->getTimestamp();

        if ($primary) {
            $data['package'] = $this->package->toApiData(false, $deep, $visited);
        }

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AppInstance';
        $metadata->setPrimaryTable([
            'name' => 'app_instances',
        ]);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'perm_type',
            'fieldName'  => 'perm_type',
            'type'       => 'string',
            'length'     => 15,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'title',
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'secret_key',
            'fieldName'  => 'secret_key',
            'type'       => 'string',
            'length'     => 40,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'auth_key',
            'fieldName'  => 'auth_key',
            'type'       => 'string',
            'length'     => 40,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'columnName' => 'settings',
            'fieldName'  => 'settings',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);

        $metadata->mapField([
            'columnName' => 'date_created',
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'package',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppPackage',
            'fetch'        => ClassMetadataInfo::FETCH_LAZY,
            'joinColumns'  => [
                [
                    'name'                 => 'package_name',
                    'referencedColumnName' => 'name',
                    'nullable'             => true,
                    'onDelete'             => 'CASCADE',
                ],
            ],
        ]);
    }
}
