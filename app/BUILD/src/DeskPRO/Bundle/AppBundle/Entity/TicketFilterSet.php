<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterSetRepository")
 * @ORM\Table(name="ticket_filter_sets")
 */
class TicketFilterSet implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @var int
     *
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $display_order;

    /**
     * @var TicketFilter[]|ArrayCollection
     *
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter",
     *     mappedBy="filter_set",
     *     cascade={"remove"}
     * )
     * @ORM\OrderBy({"display_order" = "ASC"})
     * @Serializer\Exclude()
     */
    protected $filters;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_default", type="boolean")
     */
    protected $is_default = false;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", cascade={"remove"})
     * @ORM\JoinColumn(name="person_id", onDelete="CASCADE")
     */
    protected $private_agent;

    /**
     * @var Person[]|ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinTable(
     *      name="filter_set_agents",
     *      joinColumns={
     *          @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     *      }
     * )
     */
    protected $shared_agents;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->filters       = new ArrayCollection();
        $this->shared_agents = new ArrayCollection();
        $this->display_order = 0;
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
     */
    public function addFilter(TicketFilter $filter)
    {
        $this->filters->add($filter);
        $this->setModelField('filter', $filter);

        $filter->setFilterSet($this);
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
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', (int) $display_order);
    }

    /**
     * @return bool
     */
    public function getIsDefault()
    {
        return $this->is_default === true;
    }

    /**
     * @param bool $default
     */
    public function setIsDefault($default)
    {
        $this->setModelField('is_default', (bool) $default);
    }

    /**
     * Returns the filters in the set as an array of filter ids.
     *
     * @return array the filter IDs attached.
     *
     * @Serializer\VirtualProperty
     * @Serializer\SerializedName("filters")
     */
    public function getFiltersIds()
    {
        if (!$this->filters) {
            return [];
        }

        $my_ids = [];
        foreach ($this->filters as $filter) {
            $my_ids[] = $filter->getId();
        }

        return $my_ids;
    }

    /**
     * @return Person
     */
    public function getPrivateAgent()
    {
        return $this->private_agent;
    }

    /**
     * @param Person $private_agent
     */
    public function setPrivateAgent(Person $private_agent)
    {
        $this->setModelField('private_agent', $private_agent);
    }

    /**
     * @return Person[]|ArrayCollection
     */
    public function getSharedAgents()
    {
        return $this->shared_agents;
    }

    /**
     * @param Person $agent
     */
    public function addSharedAgent(Person $agent)
    {
        $this->shared_agents->add($agent);

        $this->setModelField('shared_agents', $this->shared_agents);
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return null !== $this->private_agent;
    }
}
