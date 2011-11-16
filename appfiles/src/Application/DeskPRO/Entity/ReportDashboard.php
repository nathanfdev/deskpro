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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Dashboard of Statistics
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ReportDashboard")
 * @ORM_Mapping\Table(name="report_dashboard")
 */
class ReportDashboard extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The available chart classes
	 */
	protected static $availableChartClasses = array(
		// Am Charts
		'Application\ReportBundle\Chart\AmChart\LineChart',
		'Application\ReportBundle\Chart\AmChart\StackedLineChart',
		'Application\ReportBundle\Chart\AmChart\ColumnChart',
		'Application\ReportBundle\Chart\AmChart\StackedColumnChart',
		'Application\ReportBundle\Chart\AmChart\PieChart',
		// Desk PRO charts
		'Application\ReportBundle\Chart\DeskPRO\SimpleVariationChart',
		'Application\ReportBundle\Chart\DeskPRO\SimpleDrillDownChart',
		'Application\ReportBundle\Chart\DeskPRO\DetailedDrillDownChart',
	);

	/**
	 * @var int
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The dashboard title
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * The dashboard author
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_MAPPING\OneToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="author_id", referencedColumnName="id")
	 */
	protected $author;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="ReportDashboardStat", mappedBy="report_dashboard", cascade={"remove"})
	 */
        protected $report_dashboard_stat;

	/**
	 * The number of columns in the dashboard
	 *
	 * @var int
	 * @ORM_MAPPING\Column(name="number_columns", type="integer")
	 */
	protected $number_columns;

	/**
	 * Is the dashboard disabled
	 *
	 * @var bool
	 * @ORM_MAPPING\Column(name="disabled", type="boolean")
	 */
	protected $disabled;

	/**
	 * The dashboard creation date
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->number_columns = 4;
		$this->disabled       = false;
		$this->date_created   = new \DateTime();
		$this->report_dashboard_stat = new ArrayCollection();
	}

	/**
	 * Get the author name. Use the associated Person if one exists, otherwise
	 * its 'deskpro'
	 */
	public function getAuthorName()
	{
		if (!is_null($this->author)) {
			return $this->author->getDisplayName();
		}
		else {
			return 'deskpro';
		}
	}

	/**
	 * Get a list of available chart classes
	 *
	 * @return array
	 */
	public static function getChartClasses()
	{
		return self::$availableChartClasses;
	}

	/**
	 * Get a list of available charts. Human friendly format
	 *
	 * @return array
	 */
	public static function getChartList()
	{
		$chart_list = array();

		foreach (self::$availableChartClasses as $chart_class) {
			$chart_list[] = $chart_class::getChartLabel();
		}

		return $chart_list;
	}

}