<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at https://www.deskpro.com/eula/                            |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\FilterRepository")
 * @ORM\Table(name="filters")
 */
class Filter extends NotifyPropertyChangeEntity
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="title", type="string")
     */
    protected $title;

    /**
     * @var TermInterface
     * @ORM\Column(name="term", type="term_engine_term")
     */
    protected $term;

    /**
     * @var int
     * @ORM\Column(name="display_order", type="integer")
     */
    protected $display_order;

    /**
     * @var FilterSet
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\FilterSet", inversedBy="filters")
     */
    protected $filter_set;

    /**
     * @var FilterView[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\FilterView", mappedBy="filter")
     */
    protected $filter_views;

    /**
     * @var FilterPreference[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\FilterPreference", mappedBy="filter")
     */
    protected $filter_preferences;

    public function __construct()
    {
        $this->filter_views = new ArrayCollection();
        $this->filter_preferences = new ArrayCollection();
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
        $this->setModelField('display_order', (int)$display_order);
    }

    /**
     * @return FilterSet
     */
    public function getFilterSet()
    {
        return $this->filter_set;
    }

    /**
     * @param FilterSet $filter_set
     */
    public function setFilterSet(FilterSet $filter_set)
    {
        $this->setModelField('filter_set', $filter_set);
    }

    /**
     * @return ArrayCollection|FilterView[]
     */
    public function getFilterViews()
    {
        return $this->filter_views;
    }

    /**
     * @return ArrayCollection|FilterPreference[]
     */
    public function getFilterPreferences()
    {
        return $this->filter_preferences;
    }

    /**
     * @param FilterPreference $filter_preference
     */
    public function addFilterPreference(FilterPreference $filter_preference)
    {
        $this->filter_preferences->add($filter_preference);

        $this->setModelField('filter_preferences', $this->filter_preferences);
    }

    /**
     * @param FilterView $view
     */
    public function addFilterView(FilterView $view)
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
}
