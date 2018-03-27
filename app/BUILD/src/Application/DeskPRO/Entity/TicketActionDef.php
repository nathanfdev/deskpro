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

/**
 * @property int $id
 * @property string $action_name
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
    protected $action_name;

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
     * @var \Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef
     */
    private $_def;

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
     * Override because we need to unset the cached $_def if a setting changed.
     *
     * @param string $field
     * @param mixed  $value
     */
    protected function setModelField($field, $value)
    {
        $this->_def = null;

        return parent::setModelField($field, $value);
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
     * @return \Application\DeskPRO\Tickets\Actions\ActionDef\AbstractActionDef
     */
    public function getDef()
    {
        if ($this->_def) {
            return $this->_def;
        }

        $class      = $this->def_class;
        $this->_def = new $class($this);

        return $this->_def;
    }

    /**
     * {@inheritdoc}
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data                     = [];
        $data['id']               = $this->id;
        $data['action_name']      = $this->action_name;
        $data['def_class']        = $this->def_class;
        $data['app']              = $this->app ? $this->app->toApiData(false, false) : null;
        $data['settings']         = $this->settings ?: [];
        $data['action_title']     = $this->getDef()->getTitle();
        $data['action_class']     = $this->getDef()->getTriggerActionClass();
        $data['macro_class']      = $this->getDef()->getMacroActionClass();
        $data['builder_template'] = $this->getDef()->getActionBuilderTemplate();

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TicketActionDef';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable([
            'name'              => 'ticket_actions_def',
            'uniqueConstraints' => ['action_name_idx' => ['columns' => ['action_name']]],
        ]);

        $metadata->mapField([
            'fieldName'  => 'id',
            'columnName' => 'id',
            'type'       => 'integer',
            'id'         => true,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'fieldName'  => 'action_name',
            'columnName' => 'action_name',
            'type'       => 'string',
            'length'     => 50,
            'nullable'   => false,
        ]);

        $metadata->mapField([
            'fieldName'  => 'def_class',
            'columnName' => 'def_class',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => true,
        ]);

        $metadata->mapField([
            'fieldName'  => 'settings',
            'columnName' => 'settings',
            'type'       => 'json_array',
            'nullable'   => true,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'app',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AppInstance',
            'joinColumns'  => [
                [
                    'name'                 => 'app_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                ],
            ],
        ]);
    }
}
