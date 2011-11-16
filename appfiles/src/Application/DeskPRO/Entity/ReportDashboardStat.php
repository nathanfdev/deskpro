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
	 * The Dashboard
	 *
	 * @var \Application\DeskPRO\Entity\ReportDashboard
	 * @ORM_MAPPING\ManyToOne(targetEntity="ReportDashboard")
	 * @ORM_Mapping\JoinColumn(name="report_dashboard_id", referencedColumnName="id")
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

	public function getChartType()
	{

	}

	/**
	 * Get the Title. Constructed from the stat title and its various attributes.
	 *
	 * @return string The title.
	 */
	public function getTitle()
	{
		$stat_title = $this->getStat()->getTitle();
		$grouping_name = $this->getStat()->getGroupingName();

		$title = $stat_title;

		// Add the grouping if we need it
		if ($this->getDisplayGrouping()) {
			$title .= ' by ' . $grouping_name;
		}

		$data_points = $this->getStat()->getDefaultDataPointCount();

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
}