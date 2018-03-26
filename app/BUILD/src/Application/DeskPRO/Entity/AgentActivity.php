<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Person log items (aka user stream).
 */
class AgentActivity extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent = null;

    /**
     * @var \DateTime
     */
    protected $date_active;

    public function __construct()
    {
        $date_active         = new \DateTime();
        list($hour, $minute) = explode(':', $date_active->format('H:i'));
        $minute              = intval($minute / 5) * 5;
        $date_active->setTime($hour, $minute, 0);

        $this->setModelField('date_active', $date_active);
    }

    /**
     * @deprecated It's supposed to be $agent
     *
     * @return Person
     */
    public function getPerson()
    {
        return $this->agent;
    }

    /**
     * @deprecated It's supposed to be $agent
     *
     * @param Person $x
     */
    public function setPerson($x)
    {
        $this->agent = $x;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\AgentActivity';
        $metadata->setPrimaryTable([
            'name'    => 'agent_activity',
            'indexes' => [
                'date_created_idx' => ['columns' => ['date_active']],
            ],
        ]);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->mapField([
            'fieldName'  => 'date_active',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'date_active',
            'id'         => true,
        ]);
        //$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'agent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                0 => [
                    'name'                 => 'agent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
            'id' => true,
        ]);
    }
}
