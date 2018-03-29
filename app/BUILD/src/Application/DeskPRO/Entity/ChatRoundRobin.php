<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\People\Helpers\AgentPermissions;
use DateTime;
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
     * @param PersonRepository       $personRepo
     * @param ChatRoundRobinLogEntry $entry
     * @param int|null               $department
     *
     * @return Person|mixed
     */
    public function getNextAgent(PersonRepository $personRepo, ChatRoundRobinLogEntry $entry = null, $department = null)
    {
        if ($department && $department instanceof Department) {
            $department = $department->getId();
        }

        if ($this->routing_type === self::ROUTING_LEAST_UTILIZED) {
            return $this->getNextAgentLeastUtilized($personRepo, $entry, $department);
        }
        $agents = [];
        foreach ($this->agents as $ref) {
            $agents[] = $ref->agent;
        }

        $lastIdx = array_search($this->last, $agents, 1);
        if (false !== $lastIdx) {
            $end    = array_splice($agents, 0, $lastIdx + 1);
            $agents = array_merge($agents, $end);
        }

        $availableAgents = $personRepo->getActiveAgentIdsForUserChat();

        while ($agent = array_shift($agents)) {
            /** @var $agent Person */
            if (!$agent['is_agent'] || $agent['is_disabled'] || $agent['is_deleted']) {
                $entry && $entry->addActionSkippedDisabled($agent);
                continue;
            }

            if (!$agent->hasPerm('agent_chat.use')) {
                $entry && $entry->addActionSkippedNoPerm($agent);
                continue;
            }

            if ($department) {
                /** @var AgentPermissions $agentPermissions */
                $agentPermissions = $agent->getHelper('AgentPermissions');

                if (!in_array($department, $agentPermissions->getAllowedDepartments('chat'))) {
                    $entry && $entry->addActionSkippedNoPermDep($agent);
                    continue;
                }
            }

            if (array_search($agent->getId(), $availableAgents) === false) {
                $entry && $entry->addActionSkippedOffline($agent);
                continue;
            }

            $entry && $entry->addActionAssigned($agent);

            return $agent;
        }

        if ($entry) {
            $entry->addActionNoOnline();
        }

        return null;
    }

    /**
     * @param PersonRepository            $personRepo
     * @param ChatRoundRobinLogEntry|null $entry
     * @param null                        $department
     *
     * @return Person|null
     */
    public function getNextAgentLeastUtilized(PersonRepository $personRepo, ChatRoundRobinLogEntry $entry = null, $department = null)
    {
        $orderedAgents = [];
        foreach ($this->agents as $ref) {
            $orderedAgents[] = $ref->agent->getId();
        }

        if ($this->last) {
            $lastIdx = array_search($this->last->getId(), $orderedAgents, 1);
            if (false !== $lastIdx) {
                $end           = array_splice($orderedAgents, 0, $lastIdx + 1);
                $orderedAgents = array_merge($orderedAgents, $end);
            }
        }

        $onlineAgents    = $personRepo->getActiveAgentIdsForUserChat();
        $availableAgents = $this->agents->filter(function ($rra) use ($onlineAgents, $department) {
            /** @var $rra ChatRoundRobinAgent */
            if (!in_array($rra->getAgent()->getId(), $onlineAgents)) {
                return false;
            }
            $agentPermissions = $rra->getAgent()->getHelper('AgentPermissions');

            if (!in_array($department, $agentPermissions->getAllowedDepartments('chat'))) {
                return false;
            }

            return true;
        });

        if ($availableAgents->count() === 0) {
            if ($entry) {
                $entry->addActionNoOnline();
            }

            return null;
        }

        $min      = new DateTime();
        $min      = $min->format('U');
        $llAgents = [];
        /** @var ChatRoundRobinAgent $rra */
        foreach ($availableAgents as $rra) {
            $lastActivity = $rra->getLastActivity()->format('U');
            if ($lastActivity < $min || ($lastActivity === null && $min !== null)) {
                $min      = $lastActivity;
                $llAgents = [$rra->getAgent()];
            } elseif ($lastActivity === $min) {
                $llAgents[] = $rra->getAgent();
            }
        }
        if (count($llAgents) === 1) {
            return array_shift($llAgents);
        }
        foreach ($orderedAgents as $oid) {
            foreach ($llAgents as $agent) {
                if ($agent->getId() === $oid) {
                    return $agent;
                }
            }
        }

        return null;
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

    /**
     * @param Person $last
     */
    public function setLast($last)
    {
        $this->setModelField('last', $last);
    }
}
