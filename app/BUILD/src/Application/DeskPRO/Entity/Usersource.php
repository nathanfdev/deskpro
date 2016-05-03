<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Defines information about an external user source.
 *
 * @property $title
 * @property $type
 * @property $source_type
 * @property $lost_password_url
 * @property $options
 * @property $display_order
 * @property $is_enabled
 * @property $is_sso_auto
 * @property $is_sso_background
 * @property $auto_agent
 * @property $agent_permission_group
 * @property $user_permission_group
 * @property $app
 * @property $id
 *
 * @JMS\ExclusionPolicy("all")
 */
class Usersource extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * $this->type === TYPE_USER if it is a user interface usersource.
     */
    const TYPE_USER = 'user';

    /**
     * $this->type === TYPE_AGENT if it is an agent (including admin/reporting/billing etc) interface usersource.
     */
    const TYPE_AGENT = 'agent';

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * The title of this usersource.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title = '';

    /**
     * "user" or "agent" for now.
     *
     * the interface this usersource applies to
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $type;

    /**
     * The type of usersource this is. This maps to an adapter class.
     *
     * @var string
     */
    protected $source_type;

    /**
     * @var string
     */
    protected $lost_password_url = '';

    /**
     * Options we'll pass to the adapter. These options should be set up with some installer.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The order in which to display this source in UserBundle.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $display_order = 0;

    /**
     * True if this usersource is enabled/usable.
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_enabled = true;

    /**
     * True if this usersource is setup to be sso automatic.
     *
     * @var bool
     */
    protected $is_sso_auto = false;

    /**
     * True if this usersource is setup to be sso background.
     *
     * @var bool
     */
    protected $is_sso_background = false;

    /**
     * True if this usersource should be automatically synced.
     *
     * @var bool
     */
    protected $sync_enabled = false;

    /**
     * True if this should attempt to make users who login agents.
     *
     * @var bool
     */
    protected $auto_agent = false;

    /**
     * If attempting to make agent is successful, this will be the group.
     *
     * @var bool
     */
    protected $agent_permission_group = null;

    /**
     * Users that login with this usersource will get this group.
     *
     * @var bool
     */
    protected $user_permission_group = null;

    /**
     * @var \Application\DeskPRO\Entity\AppInstance|null
     */
    protected $app = null;

    /**
     * @var \Application\DeskPRO\Usersource\Adapter\AbstractAdapter
     */
    protected $_adapter_instance = null;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function toApiData($primary = true, $deep = true, array $visited = array())
    {
        $data           = parent::toApiData($primary, $deep, $visited);
        $data['is_sso'] = $this->is_sso_background || $this->is_sso_auto;

        return $data;
    }

    public function isSyncEnabled()
    {
        return $this->sync_enabled;
    }

    public function setSyncEnabled($enabled)
    {
        $this->setModelField('sync_enabled', (bool) $enabled);
    }

    public function makeSsoAutoOnly()
    {
        $this->setModelField('is_sso_background', false);
        $this->setModelField('is_sso_auto', true);
    }

    public function makeSsoBackgroundOnly()
    {
        $this->setModelField('is_sso_background', true);
        $this->setModelField('is_sso_auto', false);
    }

    public function disableSso()
    {
        $this->setModelField('is_sso_background', false);
        $this->setModelField('is_sso_auto', false);
    }

    public function makeSsoAutoAndBackground()
    {
        $this->setModelField('is_sso_background', true);
        $this->setModelField('is_sso_auto', true);
    }

    /**
     * Get the usersource adapter for this usersource.
     *
     * @return \Application\DeskPRO\Usersource\Adapter\AbstractAdapter
     */
    public function getAdapter()
    {
        if ($this->_adapter_instance !== null) {
            return $this->_adapter_instance;
        }

        $classname = $this->source_type;
        if (!$classname || !class_exists($classname)) {
            throw new \RuntimeException("Unknown usersource type `$classname`");
        }

        $this->_adapter_instance = new $classname($this);

        return $this->_adapter_instance;
    }

    public function __call($name, $args)
    {
        return call_user_func_array(array($this->getAdapter(), $name), $args);
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function getOption($name, $default = null)
    {
        return isset($this->options[$name]) ? $this->options[$name] : $default;
    }

    public function setOption($name, $value)
    {
        $old                  = $this->options;
        $this->options[$name] = $value;
        $this->_onPropertyChanged('options', $old, $this->options);
    }

    public function setOptions(array $options, $reset = false)
    {
        $old = $this->options;

        if ($reset) {
            $this->options = $options;
        } else {
            $this->options = array_merge($this->options, $options);
        }

        $this->_onPropertyChanged('options', $old, $this->options);
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return ucfirst(Strings::underscoreToCamelCase(Util::getBaseClassname($this->source_type)));
    }

    ############################################################################
    # Doctrine Metadata
    ############################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Usersource';
        $metadata->setPrimaryTable(['name' => 'usersources']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);

        $metadata->mapField([
            'fieldName'  => 'id',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'id',
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'title',
        ]);
        $metadata->mapField([
            'fieldName'  => 'type',
            'type'       => 'string',
            'length'     => 25,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'source_type',
            'type'       => 'string',
            'length'     => 255,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'source_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'lost_password_url',
            'type'       => 'string',
            'length'     => 1000,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'lost_password_url',
        ]);
        $metadata->mapField([
            'fieldName'  => 'options',
            'type'       => 'json_array',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'options',
        ]);
        $metadata->mapField([
            'fieldName'  => 'display_order',
            'type'       => 'integer',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'display_order',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_enabled',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_sso_auto',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_sso_auto',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_sso_background',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_sso_background',
        ]);
        $metadata->mapField([
            'fieldName'  => 'sync_enabled',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'default'    => 0,
            'nullable'   => false,
            'columnName' => 'sync_enabled',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'app',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'app_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $metadata->mapField([
            'fieldName'  => 'auto_agent',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'auto_agent',
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'agent_permission_group',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'agent_permission_group_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'user_permission_group',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Usergroup',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'user_permission_group_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }

    /**
     * @param int $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', $display_order);
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param bool $is_enabled
     */
    public function setIsEnabled($is_enabled)
    {
        $this->setModelField('is_enabled', $is_enabled);
    }

    /**
     * @return bool
     *
     * @deprecated use isEnabled
     */
    public function getIsEnabled()
    {
        return $this->isEnabled();
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->is_enabled;
    }

    /**
     * @param string $lost_password_url
     */
    public function setLostPasswordUrl($lost_password_url)
    {
        $this->setModelField('lost_password_url', $lost_password_url);
    }

    /**
     * @return string
     */
    public function getLostPasswordUrl()
    {
        return $this->lost_password_url;
    }

    /**
     * @param string $source_type
     */
    public function setSourceType($source_type)
    {
        $this->setModelField('source_type', $source_type);
    }

    /**
     * @return string
     */
    public function getSourceType()
    {
        return $this->source_type;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return AppInstance|null
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\SerializedName("source_type")
     * @JMS\Type("string")
     *
     * @return string
     */
    public function getShortSourceType()
    {
        return strtolower(Util::getBaseClassname($this->source_type));
    }

    /**
     * @JMS\VirtualProperty()
     * @JMS\Type("array")
     */
    public function getDisplayOptions()
    {
        $options = [];

        if (isset($this->options['login_custom_text'])) {
            $options['button_label'] = $this->options['login_custom_text'];
        }

        return $options;
    }
}
