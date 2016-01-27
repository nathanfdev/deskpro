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

use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Main representation of a ticket filter definition.
 *
 * Describes a criterion or set of criteria that make up a ticket filter.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\TicketFilterRepository")
 * @ORM\Table(name="custom_ticket_filters")
 * @Serializer\ExclusionPolicy("ALL")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("api_ticket_filters_get", parameters={"id" = "expr(object.getId())"})
 * )
 */
class TicketFilter implements FilterInterface, EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @Serializer\Expose()
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string")
     * @Serializer\Expose()
     * @Assert\NotNull()
     */
    protected $title;

    /**
     * @var TermInterface
     * @ORM\Column(name="term", type="term_engine_term")
     * @Serializer\Expose()
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $term;

    /**
     * @var int
     * @ORM\Column(name="display_order", type="integer")
     * @Serializer\Expose()
     */
    protected $display_order;

    /**
     * @var TicketFilterSet
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet", inversedBy="filters")
     * @ORM\JoinColumn(name="filter_set_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $filter_set;

    /**
     * @var TicketFilterView[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterView", mappedBy="filter", cascade={"remove"})
     */
    protected $filter_views;

    /**
     * @var TicketFilterPreference[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TicketFilterPreference", mappedBy="filter")
     */
    protected $filter_preferences;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_created", type="datetime")
     * @Serializer\Expose()
     */
    protected $date_created;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_updated", type="datetime")
     * @Serializer\Expose()
     */
    protected $date_updated;

    public function __construct()
    {
        $this->filter_views       = new ArrayCollection();
        $this->filter_preferences = new ArrayCollection();
        $this->setDisplayOrder(0);
        $this->setDateUpdated($updated = new \DateTime());
        $this->setDateCreated($updated);
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
     * @return TicketFilterSet
     */
    public function getFilterSet()
    {
        return $this->filter_set;
    }

    /**
     * @param TicketFilterSet $filter_set
     */
    public function setFilterSet(TicketFilterSet $filter_set)
    {
        $this->setModelField('filter_set', $filter_set);
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
     */
    public function addFilterPreference(TicketFilterPreference $filter_preference)
    {
        $this->filter_preferences->add($filter_preference);

        $this->setModelField('filter_preferences', $this->filter_preferences);
    }

    /**
     * @param TicketFilterView $view
     */
    public function addFilterView(TicketFilterView $view)
    {
        $this->filter_views->add($view);

        $this->setModelField('filter_views', $this->filter_views);
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
     */
    public function setTerm(TermInterface $term)
    {
        $this->setModelField('term', $term);
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
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);
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
     */
    public function setDateUpdated(\DateTime $date_updated)
    {
        $this->setModelField('date_updated', $date_updated);
    }
}
