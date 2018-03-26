<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Main representation of a ticket filter definition.
 *
 * Describes a criterion or set of criteria that make up a ticket filter.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterRepository")
 * @ORM\Table(name="ticket_filters2")
 *
 * @JMS\ExclusionPolicy("ALL")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilter implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique filter ID.
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
     * The filter`s title.
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
     * FQL query.
     *
     * @ORM\Column(name="query", type="string")
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $query;

    /**
     * Filter`s display order.
     *
     * @var int
     *
     * @ORM\Column(name="display_order", type="integer")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     */
    protected $displayOrder = 0;

    /**
     * Filter set this filter belongs to.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet", inversedBy="filters")
     * @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet>")
     * @JMS\SerializedName("ticket_filter_set")
     *
     * @var TicketFilterSet
     */
    protected $filterSet;

    /**
     * Constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
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
     * @return TicketFilterSet
     */
    public function getFilterSet()
    {
        return $this->filterSet;
    }

    /**
     * @param TicketFilterSet $filterSet
     *
     * @return $this
     */
    public function setFilterSet(TicketFilterSet $filterSet)
    {
        $this->setModelField('filterSet', $filterSet);

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     */
    public function setQuery($query)
    {
        $this->setModelField('query', $query);

        return $this;
    }
}
