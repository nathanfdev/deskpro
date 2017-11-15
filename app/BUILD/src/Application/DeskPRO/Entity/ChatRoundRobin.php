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

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

class ChatRoundRobin extends DomainObject
{
    const ROUTING_RR             = 0;
    const ROUTING_LEAST_UTILIZED = 1;
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Recently assigned agent.
     *
     * @var Person
     */
    protected $last = null;

    /**
     * Agents.
     *
     * @var ArrayCollection
     */
    protected $agents;

    /**
     * @var bool
     */
    protected $apply_by_default;

    /**
     * @var int
     */
    protected $routing_type;

    /**
     * Departments.
     *
     * @var Department[]
     */
    protected $departments;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var bool
     */
    protected $online_only;

    public function __construct()
    {
        $this->agents       = new ArrayCollection();
        $this->departments  = new ArrayCollection();
        $this->online_only  = false;
        $this->routing_type = 0;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function toApiData($primary = true, $deep = true, array $visited = [])
    {
        foreach ($this->agents as $agentRef) {
            $a = $agentRef->agent;
            if (!$a['is_agent'] || $a['is_disabled'] || $a['is_deleted']) {
                $this->agents->removeElement($agentRef);
            }
        }
        $data = parent::toApiData($primary, $deep, $visited);

        // otherwise may potentially be encoded in json as an object
        // because we might have removed agents above and caused keys to have gaps
        $data['agents'] = array_values($data['agents']);

        return $data;
    }

    /**
     * @param AgentDataService       $adata
     * @param ChatRoundRobinLogEntry $entry
     *
     * @return Person|mixed
     */
    public function getNextAgent(AgentDataService $adata, ChatRoundRobinLogEntry $entry = null)
    {
        $agents = [];
        foreach ($this->agents as $ref) {
            $agents[] = $ref->agent;
        }

        $lastIdx = array_search($this->last, $agents, 1);
        if (false !== $lastIdx) {
            $end    = array_splice($agents, 0, $lastIdx + 1);
            $agents = array_merge($agents, $end);
        }

        while ($agent = array_shift($agents)) {
            /** @var $agent Person */
            if (!$agent['is_agent'] || $agent['is_disabled'] || $agent['is_deleted']) {
                $entry && $entry->addActionSkippedDisabled($agent);
                continue;
            }

            if ($this['online_only'] && !$adata->isAgentOnline($agent)) {
                $entry && $entry->addActionSkippedOffline($agent);
                continue;
            }

            $entry && $entry->addActionAssigned($agent);

            return $agent;
        }

        if ($entry && $this['online_only']) {
            $entry->addActionNoOnline();
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->inheritanceType           = ClassMetadataInfo::INHERITANCE_TYPE_NONE;
        $metadata->generatorType             = ClassMetadataInfo::GENERATOR_TYPE_IDENTITY;
        $metadata->changeTrackingPolicy      = ClassMetadataInfo::CHANGETRACKING_NOTIFY;
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\ChatRoundRobin::class;
        $metadata->setPrimaryTable(['name' => 'chat_round_robin']);

        $metadata->mapField([
            'columnName' => 'id',
            'fieldName'  => 'id',
            'type'       => 'integer',
            'nullable'   => false,
            'id'         => true,
        ]);
        $metadata->mapField([
            'fieldName'  => 'title',
            'columnName' => 'title',
            'type'       => 'string',
            'length'     => 255,
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'online_only',
            'columnName' => 'online_only',
            'type'       => 'boolean',
            'nullable'   => false,
        ]);
        $metadata->mapField([
            'fieldName'  => 'routing_type',
            'columnName' => 'routing_type',
            'type'       => 'integer',
            'nullable'   => false,
        ]);

        $metadata->mapManyToOne([
            'fieldName'    => 'last',
            'dpApi'        => true,
            'targetEntity' => Person::class,
            'joinColumns'  => [
                [
                    'name'                 => 'last_agent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);

        $metadata->mapOneToMany([
            'fieldName'     => 'agents',
            'dpApi'         => true,
            'dpApiDeep'     => true,
            'targetEntity'  => ChatRoundRobinAgent::class,
            'mappedBy'      => 'robin',
            'orphanRemoval' => true,
            'orderBy'       => ['sort' => 'ASC'],
        ]);

        $metadata->mapField([
            'fieldName'  => 'apply_by_default',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'apply_by_default',
        ]);

        $metadata->mapManyToMany([
            'fieldName'    => 'departments',
            'dpApi'        => true,
            'dpApiDeep'    => true,
            'targetEntity' => Department::class,
            'cascade'      => [
                'persist',
                'merge',
            ],
            'inversedBy' => 'robins',
            'joinTable'  => [
                'name'        => 'chat_round_robin_to_department',
                'joinColumns' => [
                    0 => [
                        'name'                 => 'chat_round_robin_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                    ],
                ],
                'inverseJoinColumns' => [
                    0 => [
                        'name'                 => 'department_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                    ],
                ],
            ],
        ]);
    }
}
