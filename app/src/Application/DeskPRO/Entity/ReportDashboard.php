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
	 * Get the index of a chart by its class name
	 */
	public static function getChartClassIndex($class)
	{
		return array_search($class, self::$availableChartClasses);
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



	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE); 
		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ReportDashboard'; 
		$metadata->setPrimaryTable(array( 'name' => 'report_dashboard', )); 
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT); 
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'id', 'id' => true, )); 
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'title', )); 
		$metadata->mapField(array( 'fieldName' => 'number_columns', 'type' => 'integer', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'number_columns', )); 
		$metadata->mapField(array( 'fieldName' => 'disabled', 'type' => 'boolean', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'disabled', )); 
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'length' => NULL, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'unique' => false, 'columnName' => 'date_created', )); 
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY); 
		$metadata->mapOneToOne(array( 'fieldName' => 'author', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'cascade' => array( ), 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'author_id', 'referencedColumnName' => 'id', 'unique' => true, 'nullable' => true, 'onDelete' => NULL, 'columnDefinition' => NULL, ), ), 'orphanRemoval' => false, )); 
		$metadata->mapOneToMany(array( 'fieldName' => 'report_dashboard_stat', 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardStat', 'cascade' => array( 0 => 'remove', ), 'mappedBy' => 'report_dashboard', 'orphanRemoval' => false, ));
	}
}

