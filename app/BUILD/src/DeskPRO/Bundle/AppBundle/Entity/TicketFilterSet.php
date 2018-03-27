<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Component\Util\ListUtils;
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
     * An array of filter associations.
     *
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSetAssoc",
     *     mappedBy="filterSet",
     *     cascade={"all"}
     * )
     * @ORM\OrderBy({"displayOrder" = "ASC"})
     *
     * @var TicketFilterSetAssoc[]|ArrayCollection
     */
    protected $filterLinks;

    /**
     * @ORM\Column(name="is_global", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isGlobal = false;

    /**
     * Specific teams to share this with.
     *
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\AgentTeam", cascade={"all"})
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
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Person", cascade={"all"})
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
        $this->setModelField('filterLinks', new ArrayCollection());
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
     * @return TicketFilter[]
     */
    public function getFilters()
    {
        return ListUtils::map($this->filterLinks, function (TicketFilterSetAssoc $a) {
            return $a->getFilter();
        });
    }

    /**
     * @param TicketFilter $filter
     * @param int|null     $displayOrder Set the display order. If null and the filter is not already in the list, it will be last
     *
     * @return $this
     */
    public function addFilter(TicketFilter $filter, $displayOrder = null)
    {
        /** @var TicketFilterSetAssoc $existLink */
        $existLink = ListUtils::first($this->filterLinks, function (TicketFilterSetAssoc $a) use ($filter) {
            if ($a->getFilter() === $filter) {
                return $a;
            }

            return false;
        });

        if (!$existLink) {
            if ($displayOrder === null) {
                $displayOrder = 0;
                foreach ($this->filterLinks as $a) {
                    $displayOrder = max($displayOrder, $a->getDisplayOrder());
                }
                $displayOrder += 10;
            }

            $existLink = new TicketFilterSetAssoc($this, $filter, $displayOrder);
            $this->filterLinks->add($existLink);
            $this->setModelField('filterLinks', $this->filterLinks);
        } else {
            $existLink->setDisplayOrder($displayOrder);
        }

        return $this;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return $this
     */
    public function removeFilter(TicketFilter $filter)
    {
        foreach ($this->filterLinks as $idx => $a) {
            if ($a->getFilter() === $filter) {
                $this->filterLinks->remove($idx);
                break;
            }
        }

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
        return $this->isGlobal;
    }

    /**
     * @return bool
     */
    public function isSharedWithAgents()
    {
        return count($this->sharedAgents) >= 1;
    }

    /**
     * @return bool
     */
    public function isSharedWithTeams()
    {
        return count($this->sharedTeams) >= 1;
    }

    /**
     * Switch the share mode to global. This will clear all shared agents/teams.
     *
     * @return $this
     */
    public function enableGlobalSharing()
    {
        $this->setModelField('isGlobal', true);
        $this->clearSharedAgents();
        $this->clearSharedTeams();

        return $this;
    }

    /**
     * Disable global sharing.
     *
     * @return $this
     */
    public function disableGlobalSharing()
    {
        $this->setModelField('isGlobal', false);

        return $this;
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
        if ($this->isGlobal) {
            throw new \BadMethodCallException('Cannot add a shared team to a globally shared set. Did you want to disable global sharing via disableGlobalSharing() first?');
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
        if ($this->isGlobal) {
            throw new \BadMethodCallException('Cannot add a shared agent to a globally shared set. Did you want to disable global sharing via disableGlobalSharing() first?');
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
