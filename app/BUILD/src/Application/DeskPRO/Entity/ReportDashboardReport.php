<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\ReportBundle\Entity\ScheduledReport;
use DeskPRO\Bundle\ReportBundle\Util\VariableHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * This is just a tab, that holds a collection of widgets
 * Yes it has columns, and widgets and report with which it appears in given dashboard.
 *
 * @property int    $id
 * @property string $title
 */
class ReportDashboardReport extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * Tab title.
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title = '';

    /**
     * @var ArrayCollection|ReportWidget[]
     */
    protected $widgets;

    /**
     * @var int
     */
    protected $sort_order = 0;

    /**
     * @Assert\NotNull()
     *
     * @var ReportDashboard
     */
    protected $dashboard;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var ArrayCollection|ScheduledReport[]
     */
    protected $schedules;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->widgets   = new ArrayCollection();
        $this->schedules = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

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
     * @return ReportDashboardWidget[]|ArrayCollection
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param ReportDashboardWidget $widget
     *
     * @return $this
     */
    public function addWidget(ReportDashboardWidget $widget)
    {
        $this->widgets->add($widget);

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }

    /**
     * @param int $sort_order
     *
     * @return $this
     */
    public function setSortOrder($sort_order)
    {
        $this->sort_order = $sort_order;

        return $this;
    }

    /**
     * @return ReportDashboard
     */
    public function getDashboard()
    {
        return $this->dashboard;
    }

    /**
     * @param ReportDashboard $dashboard
     *
     * @return $this
     */
    public function setDashboard($dashboard)
    {
        $this->setModelField('dashboard', $dashboard);

        return $this;
    }

    /**
     * @return array
     */
    public function getVariables($overrides = [])
    {
        if ($overrides) {
            return VariableHelper::mergeVariables($this->variables ?: [], $overrides);
        }

        return $this->variables;
    }

    /**
     * @param array $variables
     *
     * @return $this
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * @return \DeskPRO\Bundle\ReportBundle\Entity\ScheduledReport[]|ArrayCollection
     */
    public function getSchedules()
    {
        return $this->schedules;
    }

    /**
     * @param ScheduledReport $schedule
     *
     * @return $this
     */
    public function addSchedule(ScheduledReport $schedule)
    {
        if ($schedule->getPerson()) {
            $this->removePersonSchedule($schedule->getPerson());

            $this->schedules->add($schedule);
            $schedule->setReport($this);
        }

        return $this;
    }

    /**
     * @param ScheduledReport $schedule
     *
     * @return $this
     */
    public function removeSchedule(ScheduledReport $schedule)
    {
        $this->schedules->removeElement($schedule);
        $schedule->setReport(null);

        return $this;
    }

    /**
     * @param Person $person
     *
     * @return ScheduledReport
     */
    public function getPersonSchedule(Person $person)
    {
        $schedule = $this->schedules
            ->matching(new Criteria(Criteria::expr()->eq('person', $person)))
            ->first();

        return $schedule ?: null;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function removePersonSchedule(Person $person)
    {
        $oldSchedule = $this->getPersonSchedule($person);
        if ($oldSchedule) {
            $this->removeSchedule($oldSchedule);
        }

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name' => 'report_dashboard_report',
            ]
        );
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'title',
                'type'       => 'string',
                'length'     => 255,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'title',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'sort_order',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'default'    => 0,
                'nullable'   => false,
                'columnName' => 'sort_order',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'variables',
                'type'       => 'json_array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'variables',
            ]
        );

        $metadata->mapOneToMany([
            'fieldName'    => 'widgets',
            'targetEntity' => ReportDashboardWidget::class,
            'mappedBy'     => 'report',
            'inversedBy'   => null,
            'orderBy'      => ['position' => 'ASC'],
            'cascade'      => ['persist', 'remove'],
        ]);

        $metadata->mapOneToMany(
            [
                'fieldName'     => 'schedules',
                'targetEntity'  => ScheduledReport::class,
                'cascade'       => ['remove', 'persist', 'merge'],
                'mappedBy'      => 'report',
                'fetch'         => ClassMetadataInfo::FETCH_EXTRA_LAZY,
                'orphanRemoval' => true,
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'dashboard',
                'targetEntity' => ReportDashboard::class,
                'mappedBy'     => null,
                'inversedBy'   => 'reports',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'dashboard_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
