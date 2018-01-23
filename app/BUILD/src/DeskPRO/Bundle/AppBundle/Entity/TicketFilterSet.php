<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterSetRepository")
 * @ORM\Table(name="ticket_filters2_sets")
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilterSet implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const SHARE_GLOBAL = 'global';
    const SHARE_AGENTS = 'agents';
    const SHARE_TEAMS  = 'teams';

    /**
     * The unique set id.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * The title of set.
     *
     * @ORM\Column(name="title", type="string")
     *
     * @Assert\NotBlank()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * Display order for set.
     *
     * @ORM\Column(name="display_order", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $displayOrder;

    /**
     * An array of filter object identities.
     *
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter",
     *     mappedBy="filterSet",
     *     cascade={"remove"}
     * )
     * @ORM\OrderBy({"display_order" = "ASC"})
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilter>>")
     *
     * @var TicketFilter[]|ArrayCollection
     */
    protected $filters;

    /**
     * @ORM\Column(name="share_mode", type="string", length=50)
     *
     * @JMS\Expose()
     * @JMS\Type("string"))
     *
     * @var string
     */
    protected $shareMode = self::SHARE_GLOBAL;

    /**
     * Specific teams to share this with.
     *
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinTable(
     *      name="ticket_filters2_set_teams",
     *      joinColumns={
     *          @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="agent_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var AgentTeam[]|ArrayCollection
     */
    protected $sharedTeams;

    /**
     * Specific agents to share this with.
     *
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinTable(
     *      name="ticket_filters2_set_agents",
     *      joinColumns={
     *          @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]|ArrayCollection
     */
    protected $sharedAgents;

    public function __construct()
    {
        $this->setModelField('filters', new ArrayCollection());
        $this->setModelField('sharedAgents', new ArrayCollection());
        $this->setModelField('sharedTeams', new ArrayCollection());
        $this->setModelField('displayOrder', 0);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TicketFilter[]|ArrayCollection
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return $this
     */
    public function addFilter(TicketFilter $filter)
    {
        $this->filters->add($filter);
        $filter->setFilterSet($this);

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }

    /**
     * @param int $displayOrder
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->setModelField('displayOrder', (int) $displayOrder);

        return $this;
    }

    /**
     * @return bool
     */
    public function isGlobal()
    {
        return $this->shareMode === self::SHARE_GLOBAL;
    }

    /**
     * @return bool
     */
    public function isSharedWithAgents()
    {
        return $this->shareMode === self::SHARE_AGENTS;
    }

    /**
     * @return bool
     */
    public function isSharedWithTeams()
    {
        return $this->shareMode === self::SHARE_TEAMS;
    }

    /**
     * Switch the share mode to global. This will clear all shared agents/teams.
     *
     * @return $this
     */
    public function enableGlobalSharing()
    {
        $this->setModelField('shareMode', self::SHARE_GLOBAL);
        $this->clearSharedAgents();
        $this->clearSharedTeams();

        return $this;
    }

    /**
     * Enable agent sharing.
     *
     * @return $this
     */
    public function enableAgentSharing()
    {
        $this->setModelField('shareMode', self::SHARE_AGENTS);
        $this->clearSharedTeams();
    }

    /**
     * Enable agent sharing.
     *
     * @return $this
     */
    public function enableTeamSharing()
    {
        $this->setModelField('shareMode', self::SHARE_TEAMS);
        $this->clearSharedAgents();
    }

    /**
     * @return AgentTeam[]|ArrayCollection
     */
    public function getSharedTeams()
    {
        return $this->sharedTeams;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function addSharedTeam(AgentTeam $team)
    {
        if ($this->shareMode !== self::SHARE_TEAMS) {
            throw new \BadMethodCallException('Cannot add a shared team to a non-team shared set. Did you want to switch the share mode with enableTeamSharing()?');
        }
        $this->sharedTeams->add($team);
        $this->setModelField('sharedTeams', $this->sharedTeams);

        return $this;
    }

    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function removeSharedTeam(AgentTeam $team)
    {
        $this->sharedTeams->removeElement($team);
        $this->setModelField('sharedTeams', $this->sharedTeams);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearSharedTeams()
    {
        $this->sharedTeams->clear();
        $this->setModelField('sharedTeams', $this->sharedTeams);

        return $this;
    }

    /**
     * @return Person[]|ArrayCollection
     */
    public function getSharedAgents()
    {
        return $this->sharedAgents;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function addSharedAgent(Person $agent)
    {
        if ($this->shareMode !== self::SHARE_AGENTS) {
            throw new \BadMethodCallException('Cannot add a shared agent to a non-agent shared set. Did you want to switch the share mode with enableAgentSharing()?');
        }
        $this->sharedAgents->add($agent);
        $this->setModelField('sharedAgents', $this->sharedAgents);

        return $this;
    }

    /**
     * @param Person $agent
     *
     * @return $this
     */
    public function removeSharedAgent(Person $agent)
    {
        $this->sharedAgents->removeElement($agent);
        $this->setModelField('sharedAgents', $this->sharedAgents);

        return $this;
    }

    /**
     * @return $this
     */
    public function clearSharedAgents()
    {
        $this->sharedAgents->clear();
        $this->setModelField('sharedAgents', $this->sharedAgents);

        return $this;
    }
}
