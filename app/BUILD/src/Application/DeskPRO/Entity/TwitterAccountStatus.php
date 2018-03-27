<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * A Twitter Account Status.
 */
class TwitterAccountStatus extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var TwitterAccount
     */
    protected $account;

    /**
     * @var TwitterStatus
     */
    protected $status;

    /**
     * @var Person
     */
    protected $agent;

    /**
     * @var AgentTeam
     */
    protected $agent_team;

    /**
     * @var Person
     */
    protected $action_agent;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * @var string|null
     */
    protected $status_type;

    /**
     * @var bool
     */
    protected $is_archived = false;

    /**
     * @var bool
     */
    protected $is_favorited = false;

    /**
     * @var TwitterAccountStatus
     */
    protected $retweeted;

    /**
     * @var TwitterAccountStatus
     */
    protected $in_reply_to;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $replies;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $notes;

    public function __construct()
    {
        $this->date_created = new \DateTime();

        $this->notes   = new \Doctrine\Common\Collections\ArrayCollection();
        $this->replies = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function setAgentId($agent_id)
    {
        if ($agent_id) {
            $agent = App::getOrm()->find('DeskPRO:Person', $agent_id);
            if ($agent && $agent->is_agent) {
                $this->setModelField('agent', $agent);
            } else {
                $this->setModelField('agent', null);
            }
        } else {
            $this->setModelField('agent', null);
        }
        $this->setModelField('agent_team', null);
    }

    public function setAgentTeamId($agent_team_id)
    {
        if ($agent_team_id) {
            $team = App::getOrm()->find('DeskPRO:AgentTeam', $agent_team_id);
            if ($team) {
                $this->setModelField('agent_team', $team);
            } else {
                $this->setModelField('agent_team', null);
            }
        } else {
            $this->setModelField('agent_team', null);
        }
        $this->setModelField('agent', null);
    }

    public function setStatus(TwitterStatus $status)
    {
        $this->setModelField('status', $status);
        $this->setModelField('date_created', $status->date_created);
    }

    public function canRetweet()
    {
        return
            !$this->status->isMessage()
            && $this->status->getUserId() != $this->account->getUserId()
            && (!$this->status->isRetweet() || $this->status->retweet->getUserId() != $this->account->getUserId())
            ;
    }

    public function isFromSelf()
    {
        return
            $this->status->getUserId() == $this->account->getUserId()
            ;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\TwitterAccountStatus';
        $metadata->setPrimaryTable([
            'name'    => 'twitter_accounts_statuses',
            'indexes' => [
                'account_type_archived_idx' => ['columns' => ['account_id', 'status_type', 'is_archived']],
                'account_archived_idx'      => ['columns' => ['account_id', 'is_archived']],
            ],
        ]);
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
            'fieldName'  => 'date_created',
            'type'       => 'datetime',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'date_created',
        ]);
        $metadata->mapField([
            'fieldName'  => 'status_type',
            'type'       => 'string',
            'length'     => 25,
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => true,
            'columnName' => 'status_type',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_archived',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_archived',
        ]);
        $metadata->mapField([
            'fieldName'  => 'is_favorited',
            'type'       => 'boolean',
            'precision'  => 0,
            'scale'      => 0,
            'nullable'   => false,
            'columnName' => 'is_favorited',
        ]);
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
        $metadata->mapManyToOne([
            'fieldName'    => 'account',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccount',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'account_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'status',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterStatus',
            'mappedBy'     => null,
            'inversedBy'   => 'account_statuses',
            'joinColumns'  => [
                [
                    'name'                 => 'status_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'cascade',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'agent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'agent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'agent_team',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\AgentTeam',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'agent_team_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'action_agent',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'action_agent_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'retweeted',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountStatus',
            'mappedBy'     => null,
            'inversedBy'   => null,
            'joinColumns'  => [
                [
                    'name'                 => 'retweeted_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
             ],
        ]);
        $metadata->mapManyToOne([
            'fieldName'    => 'in_reply_to',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountStatus',
            'mappedBy'     => null,
            'inversedBy'   => 'replies',
            'joinColumns'  => [
                [
                    'name'                 => 'in_reply_to_id',
                    'referencedColumnName' => 'id',
                    'nullable'             => true,
                    'onDelete'             => 'set null',
                    'columnDefinition'     => null,
                ],
            ],
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'replies',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountStatus',
            'mappedBy'     => 'in_reply_to',
        ]);
        $metadata->mapOneToMany([
            'fieldName'    => 'notes',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\TwitterAccountStatusNote',
            'mappedBy'     => 'account_status',
        ]);
    }
}
