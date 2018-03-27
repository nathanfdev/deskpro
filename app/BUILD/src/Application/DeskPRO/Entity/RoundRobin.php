<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\DependencyInjection\SystemServices\AgentDataService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Article.
 */
class RoundRobin extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Recently assigned agent.
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $last = null;

    /**
     * Agents.
     *
     * @var ArrayCollection
     */
    protected $agents;

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
        $this->agents      = new ArrayCollection();
        $this->online_only = false;
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
     * @param RoundRobinLogEntry $entry
     *
     * @return Person|mixed
     */
    public function getNextAgent(AgentDataService $adata, RoundRobinLogEntry $entry = null)
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
        $metadata->customRepositoryClassName = 'Application\\DeskPRO\\EntityRepository\\RoundRobin';
        $metadata->setPrimaryTable(['name' => 'round_robin']);

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

        $metadata->mapManyToOne([
            'fieldName'    => 'last',
            'dpApi'        => true,
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
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
            'targetEntity'  => 'Application\\DeskPRO\\Entity\\RoundRobinAgent',
            'mappedBy'      => 'robin',
            'orphanRemoval' => true,
            'orderBy'       => ['sort' => 'ASC'],
        ]);
    }
}
