<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterPreferenceRepository")
 * @ORM\Table(name="ticket_filter_preferences")
 * @JMS\ExclusionPolicy("ALL")
 *
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilterPreference implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var TicketFilter
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilter", inversedBy="filter_preferences")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilter>")
     */
    protected $filter;

    /**
     * @var TicketFilterView
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterView")
     */
    protected $filter_view;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person", cascade={"remove"})
     * @ORM\JoinColumn(name="person_id")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     */
    protected $agent;

    /**
     * @var int
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $display_order;

    /**
     * @var string
     * @ORM\Column(name="main_grouping", type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $main_grouping;

    /**
     * @var string
     * @ORM\Column(name="result_grouping", type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $result_grouping;

    /**
     * @var bool
     * @ORM\Column(name="show_sla", type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     */
    protected $show_sla;

    public function __construct()
    {
        $this->display_order = 0;
        $this->show_sla      = false;
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return TicketFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return TicketFilterPreference
     */
    public function setFilter(TicketFilter $filter)
    {
        $this->setModelField('filter', $filter);
        $filter->addFilterPreference($this);

        return $this;
    }

    /**
     * @return TicketFilterView
     */
    public function getFilterView()
    {
        return $this->filter_view;
    }

    /**
     * @param TicketFilterView $filter_view
     *
     * @return $this
     */
    public function setFilterView(TicketFilterView $filter_view)
    {
        $this->setModelField('filter_view', $filter_view);

        return $this;
    }

    /**
     * @return Person
     */
    public function getAgent()
    {
        return $this->agent;
    }

    /**
     * @param Person $agent
     *
     * @return TicketFilterPreference
     */
    public function setAgent(Person $agent = null)
    {
        $this->setModelField('agent', $agent);

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
     * @return array
     */
    public function getMainGrouping()
    {
        return $this->main_grouping;
    }

    /**
     * @param string $main_grouping
     *
     * @return $this
     */
    public function setMainGrouping($main_grouping)
    {
        $this->setModelField('main_grouping', $main_grouping);

        return $this;
    }

    /**
     * @return array
     */
    public function getResultGrouping()
    {
        return $this->result_grouping;
    }

    /**
     * @param string $result_grouping
     *
     * @return $this
     */
    public function setResultGrouping($result_grouping)
    {
        $this->setModelField('result_grouping', $result_grouping);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasShowSla()
    {
        return $this->show_sla;
    }

    /**
     * @param bool $show_sla
     *
     * @return $this
     */
    public function setShowSla($show_sla)
    {
        $this->setModelField('show_sla', (bool) $show_sla);

        return $this;
    }

    public function isPrivate()
    {
        return null !== $this->agent;
    }
}
