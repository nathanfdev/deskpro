<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * The stats for a dashboard
 *
 */
class ReportDashboardStat extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_MAPPING\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * The Dashboard
	 *
	 * @var \Application\DeskPRO\Entity\ReportDashboard
	 * @ORM_MAPPING\ManyToOne(targetEntity="ReportDashboard", inversedBy="report_dashboard_stat")
	 */
	protected $report_dashboard;

	/**
	 * The Stat
	 *
	 * @var \Application\DeskPRO\Entity\Stat
	 * @ORM_MAPPING\ManyToOne(targetEntity="Stat")
	 */
	protected $stat;

	/**
	 * The class responsible for the view
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="view_class", type="string", length=255)
	 */
	protected $view_class;

	/**
	 * Number of slots the stat takes up in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="grid_slots", type="integer")
	 */
	protected $grid_slots;

	/**
	 * Number of cols the stat takes up in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="grid_columns", type="integer")
	 */
	protected $grid_columns;

	/**
	 * Number of rows the stat takes up in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="grid_rows", type="integer")
	 */
	protected $grid_rows;

	/**
	 * The order of the stat in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="slot_number", type="integer")
	 */
	protected $slot_number;

	/**
	 * @var int
	 * @ORM_MAPPING\Column(name="number_data_points", type="integer")
	 */
	protected $number_data_points;

	/**
	 * Indicates is the legend should be show on the chart
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="show_legend", type="boolean")
	 */
	protected $show_legend;

	/**
	 * Indicates if the ungrouped or grouped data should be displayed
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="display_grouping", type="boolean")
	 */
	protected $display_grouping;

	/**
	 * The dashboard creation date
	 *
	 * @var \DateTime
	 */
	protected $date_created;

	public function __construct()
	{
		$this->grid_slots   = 1;
		$this->grid_columns = 1;
		$this->grid_rows    = 1;
		$this->show_legend  = 0;
		$this->view_class   = "Application\ReportBundle\Chart\AmChart\LineChart";
		$this->date_created = new \DateTime();
	}

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	public function createView()
	{
		$viewClass = $this->getViewClass();

		$view = new $viewClass();

		return $view;
	}

	/**
	 * @return \Application\DeskPRO\Entity\Stat
	 */
	public function getStat()
	{
		return $this->stat;
	}

	/**
	 * Get the number of data points
	 *
	 * @return int The number of data points to display
	 */
	public function getDefaultNumberDataPoints()
	{
		return $this->getStat()->getDefaultDataPointCount();
	}

	/**
	 * Get the Title. Constructed from the stat title and its various attributes.
	 *
	 * @return string The title.
	 */
	public function getDefaultTitle()
	{
		$stat_title = $this->getStat()->getTitle();

		return $stat_title;
	}

	/**
	 * Get the Title. Constructed from the stat title and its various attributes.
	 *
	 * @return string The title.
	 */
	public function getDisplayTitle()
	{
		$title = $this->getTitle();
		if ($title) {
			return $title;
		}

		if (strlen($title) === 0) {
			$title = $this->getDefaultTitle();
		}

		$grouping_name = $this->getStat()->getGroupingName();

		// Add the grouping if we need it
		if ($this->getDisplayGrouping()) {
			$title .= ' by ' . $grouping_name;
		}

		$data_points = $this->getNumberDataPoints();

		// Add the update period
		$title .= ' - Last ' . $data_points . ' ';

		return $title;
	}

	/**
	 * Sets the chart type
	 *
	 * @param string $chart_type
	 */
	public function setChartType($chart_type)
	{
		$chart_list = ReportDashboard::getChartClasses();

		$chart_class = $chart_list[$chart_type];

		$this->setViewClass($chart_class);
	}

	/**
	 * Get the chart type from the view_class
	 *
	 * @return string
	 */
	public function getChartType()
	{
		$chart_list = ReportDashboard::getChartClasses();

		$index = 0;
		foreach ($chart_list as $chart) {
			if ($chart === $this->getViewClass()) {
				break;
			}
			$index++;
		}

		return $index;
	}



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ReportDashboardStat';
		$metadata->setPrimaryTable(array( 'name' => 'report_dashboard_stat', ));
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'view_class', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'view_class', ));
		$metadata->mapField(array( 'fieldName' => 'grid_slots', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'grid_slots', ));
		$metadata->mapField(array( 'fieldName' => 'grid_columns', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'grid_columns', ));
		$metadata->mapField(array( 'fieldName' => 'grid_rows', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'grid_rows', ));
		$metadata->mapField(array( 'fieldName' => 'slot_number', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'slot_number', ));
		$metadata->mapField(array( 'fieldName' => 'number_data_points', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'number_data_points', ));
		$metadata->mapField(array( 'fieldName' => 'show_legend', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'show_legend', ));
		$metadata->mapField(array( 'fieldName' => 'display_grouping', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'display_grouping', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'report_dashboard', 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboard', 'mappedBy' => NULL, 'inversedBy' => 'report_dashboard_stat', 'joinColumns' => array( 0 => array( 'name' => 'report_dashboard_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapManyToOne(array( 'fieldName' => 'stat', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Stat', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'stat_id', 'referencedColumnName' => 'id', 'nullable' => true, 'onDelete' => 'cascade', 'columnDefinition' => NULL, ), ),  ));
	}
}
