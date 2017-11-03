<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\Common\Collections\ArrayCollection;
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
 * @ORM\Table(name="custom_ticket_filters")
 *
 * @JMS\ExclusionPolicy("ALL")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class TicketFilter implements FilterInterface, EntityInterface, NotifyPropertyChanged
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
     *  Term object including whole spectre of operations to filter tickets.
     *
     * @ORM\Column(name="term", type="term_engine_term")
     *
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @JMS\Expose()
     * @JMS\Type("DeskPRO\Bundle\AppBundle\TermEngine\Term\AbstractTerm")
     *
     * @var TermInterface
     */
    protected $term;

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
    protected $display_order = 0;

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
    protected $filter_set;

    /**
     * Views attached this filter.
     *
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterView", mappedBy="filter", cascade={"remove"})
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilterView>>")
     *
     * @var TicketFilterView[]|ArrayCollection
     */
    protected $filter_views;

    /**
     * Preferences associated with this filter.
     *
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference", mappedBy="filter")
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference>>")
     *
     * @var TicketFilterPreference[]|ArrayCollection
     */
    protected $filter_preferences;

    /**
     * Date when this filter was created.
     *
     * @ORM\Column(name="date_created", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Date when this filter was updated.
     *
     * @ORM\Column(name="date_updated", type="datetime")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->setModelField('filter_views', new ArrayCollection());
        $this->setModelField('filter_preferences', new ArrayCollection());
        $this->setModelField('date_created', new \DateTime());
        $this->setModelField('date_updated', new \DateTime());
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
     * @return TicketFilterSet
     */
    public function getFilterSet()
    {
        return $this->filter_set;
    }

    /**
     * @param TicketFilterSet $filter_set
     *
     * @return $this
     */
    public function setFilterSet(TicketFilterSet $filter_set)
    {
        $this->setModelField('filter_set', $filter_set);

        return $this;
    }

    /**
     * @return ArrayCollection|TicketFilterView[]
     */
    public function getFilterViews()
    {
        return $this->filter_views;
    }

    /**
     * @return ArrayCollection|TicketFilterPreference[]
     */
    public function getFilterPreferences()
    {
        return $this->filter_preferences;
    }

    /**
     * @param TicketFilterPreference $filter_preference
     *
     * @return $this
     */
    public function addFilterPreference(TicketFilterPreference $filter_preference)
    {
        $this->filter_preferences->add($filter_preference);
        $this->setModelField('filter_preferences', $this->filter_preferences);

        return $this;
    }

    /**
     * @param TicketFilterView $view
     *
     * @return $this
     */
    public function addFilterView(TicketFilterView $view)
    {
        $this->filter_views->add($view);
        $this->setModelField('filter_views', $this->filter_views);

        return $this;
    }

    /**
     * @return TermInterface
     */
    public function getTerm()
    {
        return $this->term;
    }

    /**
     * @param TermInterface $term
     *
     * @return $this
     */
    public function setTerm(TermInterface $term)
    {
        $this->setModelField('term', $term);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $date_updated
     *
     * @return $this
     */
    public function setDateUpdated(\DateTime $date_updated)
    {
        $this->setModelField('date_updated', $date_updated);

        return $this;
    }

    public function __toString()
    {
        return (string) $this->getId();
    }
}
