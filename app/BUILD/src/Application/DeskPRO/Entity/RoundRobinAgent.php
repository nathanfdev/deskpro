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
 * Article.
 */
class RoundRobinAgent extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var \Application\DeskPRO\Entity\RoundRobin
     */
    protected $robin;

    /**
     * Next agent in queue.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $agent;

    /**
     * Sort field.
     *
     * @var int
     */
    protected $sort;

    public function __construct()
    {
        $this['sort'] = 0;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        //		$data = parent::toApiData($primary, $deep, $visited);
        $data = [
            'id' => $this->agent ? $this->agent['id'] : null,
        ];

        return $data;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'round_robin_agents']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\RoundRobinAgent';

        $metadata->mapField([
            'fieldName'  => 'sort',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'sort',
        ]);

        $metadata->mapOneToOne([
            'id'           => true,
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
        ]);

        $metadata->mapManyToOne([
            'id'           => true,
            'fieldName'    => 'robin',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\RoundRobin',
            'mappedBy'     => null,
            'inversedBy'   => 'agents',
            'joinColumns'  => [
                0 => [
                    'name'                 => 'robin_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => false,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
    }
}
