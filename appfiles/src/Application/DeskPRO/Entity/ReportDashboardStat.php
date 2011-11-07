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
	 * The dashboard creation date
	 *
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created", type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->grid_slots   = 1;
		$this->date_created = new \DateTime();
	}
	
	public function createView()
	{
		$viewClass = $this->getViewClass();
		
		$view = new $viewClass();
		
		return $view;
	}
	
}