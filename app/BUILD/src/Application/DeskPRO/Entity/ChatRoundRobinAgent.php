<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Domain\DomainObject;
use DateTime;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ChatRoundRobinAgent extends DomainObject
{
    /**
     * @var ChatRoundRobin
     */
    protected $robin;

    /**
     * Next agent in queue.
     *
     * @var Person
     */
    protected $agent;

    /**
     * Sort field.
     *
     * @var int
     */
    protected $sort;

    /**
     * @var DateTime
     */
    protected $last_activity;

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
        $metadata->setPrimaryTable(['name' => 'chat_round_robin_agents']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\ChatRoundRobinAgent::class;

        $metadata->mapField([
            'fieldName'  => 'sort',
            'type'       => 'integer',
            'nullable'   => false,
            'columnName' => 'sort',
        ]);

        $metadata->mapField([
            'fieldName'  => 'last_activity',
            'type'       => 'datetime',
            'nullable'   => true,
            'columnName' => 'last_activity',
        ]);

        $metadata->mapOneToOne([
            'id'           => true,
            'fieldName'    => 'agent',
            'targetEntity' => Person::class,
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
            'targetEntity' => ChatRoundRobin::class,
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

    /**
     * @return DateTime
     */
    public function getLastActivity()
    {
        return $this->last_activity;
    }

    /**
     * @param DateTime $lastActivity
     */
    public function setLastActivity(DateTime $lastActivity = null)
    {
        if (!$lastActivity) {
            $lastActivityTime = time();
        } else {
            $lastActivityTime = $lastActivity->getTimestamp();
        }
        // Round down the time to the closest 5 minutes
        $lastActivityTime = floor($lastActivityTime / 300) * 300;

        $lastActivity = date_create('@'.$lastActivityTime);

        $this->setModelField('last_activity', $lastActivity);
    }
}
