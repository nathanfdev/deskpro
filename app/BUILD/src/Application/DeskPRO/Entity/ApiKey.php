<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\ApiKeyAction;
use DeskPRO\Bundle\AppBundle\Entity\ApiLog;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Util\DpStrings;
use Orb\Util\Strings;

/**
 * @property int $id
 * @property string $code
 * @property string note
 * @property string $keyString
 * @property Person $person
 * @property array $flags
 *
 * @method ApiKeyAction[] getActions()
 */
class ApiKey extends DomainObject
{
    const FLAG_ADMIN_MANAGE = 'admin_manage';
    const FLAG_SUPER_KEY    = 'super';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person;

    /**
     * A note or description about the key (ie what its used for).
     *
     * @var string
     */
    protected $note = '';

    /**
     * @var array
     */
    protected $flags = [];

    /**
     * @var ArrayCollection
     */
    protected $logs;

    /**
     * @var ArrayCollection
     */
    protected $api_logs;

    /**
     * @var ArrayCollection
     */
    protected $actions;

    public function __construct()
    {
        $this->regenerateApiKey();
        $this->logs     = new ArrayCollection();
        $this->actions  = new ArrayCollection();
        $this->api_logs = new ArrayCollection();
    }

    /**
     * Regenerate the API key.
     */
    public function regenerateApiKey()
    {
        $this['code'] = DpStrings::random(25, Strings::CHARS_KEY);
    }

    /**
     * Get a "key string". This is a combined ID and code like id:code
     * that is used in auth lookup.
     *
     * @return string
     */
    public function getKeyString()
    {
        return $this->id.':'.$this->code;
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function isFlagSet($flag)
    {
        return in_array($flag, $this->flags);
    }

    /**
     * @param bool  $primary
     * @param bool  $deep
     * @param array $visited
     *
     * @return array
     */
    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        $data              = parent::toApiData($primary, false, $visited);
        $data['keyString'] = $this->getKeyString();
        $data['person']    = $this->person ? $this->person['id'] : null;
        foreach ($this->flags as $f) {
            $data[$f] = true;
        }

        return $data;
    }

    /**
     * @param ApiKeyAction $action
     *
     * @return $this
     */
    public function addApiKeyAction(ApiKeyAction $action)
    {
        if ($this->actions->contains($action)) {
            return;
        }
        $this->actions->add($action);
        $action->setKey($this);
        $this->_onPropertyChanged('actions', $this->actions, $this->actions);

        return $this;
    }

    /**
     * @param ApiLog $log
     *
     * @return $this
     */
    public function addApiLog(ApiLog $log)
    {
        $this->api_logs->add($log);
        $log->setKey($this);

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\ApiKey';
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;

        $metadata->setPrimaryTable(
            [
                'name' => 'api_keys',
            ]
        );

        $metadata->mapField(
            [
                'columnName' => 'id',
                'fieldName'  => 'id',
                'type'       => 'integer',
                'id'         => true,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'code',
                'fieldName'  => 'code',
                'type'       => 'string',
                'length'     => 25,
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'note',
                'fieldName'  => 'note',
                'type'       => 'text',
                'nullable'   => false,
            ]
        );
        $metadata->mapField(
            [
                'columnName' => 'flags',
                'fieldName'  => 'flags',
                'type'       => 'simple_array',
                'nullable'   => true,
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'     => 'logs',
                'targetEntity'  => 'Application\\DeskPRO\\Entity\\ApiKeyLog',
                'mappedBy'      => 'key',
                'cascade'       => ['remove', 'persist', 'merge'],
                'orphanRemoval' => true,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'     => 'actions',
                'targetEntity'  => 'DeskPRO\\Bundle\\AppBundle\\Entity\\ApiKeyAction',
                'mappedBy'      => 'key',
                'cascade'       => ['remove', 'persist', 'merge'],
                'orphanRemoval' => true,
            ]
        );

        $metadata->mapOneToMany(
            [
                'fieldName'    => 'api_logs',
                'targetEntity' => 'DeskPRO\\Bundle\\AppBundle\\Entity\\ApiLog',
                'mappedBy'     => 'key',
                'inversedBy'   => null,
                'orderBy'      => ['id' => 'DESC'],
                'cascade'      => ['persist', 'remove'],
            ]
        );
    }
}
