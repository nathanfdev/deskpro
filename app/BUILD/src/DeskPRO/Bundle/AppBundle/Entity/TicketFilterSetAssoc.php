<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity
 * @ORM\Table(name="ticket_filters2_assoc")
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilterSetAssoc implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet")
     * @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var \DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet
     */
    protected $filterSet;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter")
     * @ORM\JoinColumn(name="filter_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @var \DeskPRO\Bundle\AppBundle\Entity\TicketFilter
     */
    protected $filter;

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
    protected $displayOrder = 0;

    /**
     * TicketFilterSetAssoc constructor.
     *
     * @param TicketFilterSet $filterSet
     * @param TicketFilter    $filter
     * @param int             $displayOrder
     */
    public function __construct(TicketFilterSet $filterSet, TicketFilter $filter, $displayOrder = 0)
    {
        $this->setModelField('filterSet', $filterSet);
        $this->setModelField('filter', $filter);
        $this->setModelField('displayOrder', $displayOrder);
    }

    /**
     * @return mixed|string
     */
    public function getId()
    {
        return $this->filterSet->getId().':'.$this->filter->getId();
    }

    /**
     * @return TicketFilterSet
     */
    public function getFilterSet()
    {
        return $this->filterSet;
    }

    /**
     * @return TicketFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param int $order
     *
     * @return $this
     */
    public function setDisplayOrder($displayOrder)
    {
        $this->setModelField('displayOrder', (int) $displayOrder);

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->displayOrder;
    }
}
