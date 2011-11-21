<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

/**
 * The stats for a dashboard
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ReportDashboardStat")
 * @ORM_Mapping\Table(name="report_dashboard_stat")
 */
class ReportDashboardStat extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_MAPPING\Column(name="title", type="string", length="255")
	 */
	protected $title;
	
	/**
	 * The Dashboard
	 *
	 * @var \Application\DeskPRO\Entity\ReportDashboard
	 * @ORM_MAPPING\ManyToOne(targetEntity="ReportDashboard", inversedBy="report_dashboard_stat")
	 * @ORM_Mapping\JoinColumn(name="report_dashboard_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $report_dashboard;

	/**
	 * The Stat
	 *
	 * @var \Application\DeskPRO\Entity\Stat
	 * @ORM_MAPPING\ManyToOne(targetEntity="Stat")
	 * @ORM_Mapping\JoinColumn(name="stat_id", referencedColumnName="id")
	 */
	protected $stat;

	/**
	 * The class responsible for the view
	 *
	 * @var string
	 * @ORM_MAPPING\Column(name="view_class", type="string", length="500")
	 */
	protected $view_class;

	/**
	 * Number of slots the stat takes up in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="grid_slots", type="smallint")
	 */
	protected $grid_slots;

	/**
	 * The order of the stat in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="slot_number", type="smallint")
	 */
	protected $slot_number;

	/**
	 * @var int
	 * @ORM_MAPPING\Column(name="number_data_points", type="integer")
	 */
	protected $number_data_points;
	
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
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->grid_slots   = 1;
		$this->view_class   = "Application\ReportBundle\Chart\AmChart\LineChart";
		$this->date_created = new \DateTime();
	}

	public function createView()
	{
		$viewClass = $this->getViewClass();

		$view = new $viewClass();

		return $view;
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
		switch (strtolower($this->getStat()->getPeriod())) {
			case 'daily':
				$title .= ' ' . (($data_points > 1) ? 'Days' : 'Day');
				break;
			case 'monthly':
				$title .= ' ' . (($data_points > 1) ? 'Months' : 'Month');
				break;
			case 'yearly':
				$title .= ' ' . (($data_points > 1) ? 'Years' : 'Year');
				break;
		}

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
}