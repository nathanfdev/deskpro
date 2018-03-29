<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterSetRepository")
 * @ORM\Table(name="ticket_filter_sets")
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
    protected $display_order;

    /**
     * An array of filter object identities.
     *
     * @ORM\OneToMany(
     *     targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter",
     *     mappedBy="filter_set",
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
     * True if filter is default.
     *
     * @ORM\Column(name="is_default", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $is_default = false;

    /**
     * Person id if this stuff belongs to somebody privately.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", cascade={"remove"})
     * @ORM\JoinColumn(name="person_id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $private_agent;

    /**
     * Ids of agent shares this filter set.
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
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Person>>")
     *
     * @var Person[]|ArrayCollection
     */
    protected $shared_agents;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('filters', new ArrayCollection());
        $this->setModelField('shared_agents', new ArrayCollection());
        $this->setModelField('display_order', 0);
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
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return $this
     */
    public function setDisplayOrder($display_order)
    {
        $this->setModelField('display_order', (int) $display_order);

        return $this;
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
     *
     * @return $this
     */
    public function setIsDefault($default)
    {
        $this->setModelField('is_default', (bool) $default);

        return $this;
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
     *
     * @return $this
     */
    public function setPrivateAgent(Person $private_agent)
    {
        $this->setModelField('private_agent', $private_agent);

        return $this;
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
     *
     * @return $this
     */
    public function addSharedAgent(Person $agent)
    {
        $this->shared_agents->add($agent);
        $this->setModelField('shared_agents', $this->shared_agents);

        return $this;
    }

    /**
     * @return bool
     */
    public function isPrivate()
    {
        return null !== $this->private_agent;
    }
}
