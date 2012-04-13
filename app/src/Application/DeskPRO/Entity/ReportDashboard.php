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
	 */
	protected $id = null;

	/**
	 * The dashboard title
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * The dashboard author
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $author;

	/**
	 */
	protected $report_dashboard_stat;

	/**
	 * The number of columns in the dashboard
	 *
	 * @var int
	 */
	protected $number_columns;

	/**
	 * Is the dashboard disabled
	 *
	 * @var bool
	 */
	protected $disabled;

	/**
	 * @var int
	 */
	protected $display_order = 0;

	/**
	 * The dashboard creation date
	 *
	 * @var \DateTime
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
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
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
		$metadata->mapField(array( 'fieldName' => 'id', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'id', 'id' => true, ));
		$metadata->mapField(array( 'fieldName' => 'title', 'type' => 'string', 'length' => 255, 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'title', ));
		$metadata->mapField(array( 'fieldName' => 'number_columns', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'number_columns', ));
		$metadata->mapField(array( 'fieldName' => 'disabled', 'type' => 'boolean', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'disabled', ));
		$metadata->mapField(array( 'fieldName' => 'display_order', 'type' => 'integer', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'display_order', ));
		$metadata->mapField(array( 'fieldName' => 'date_created', 'type' => 'datetime', 'precision' => 0, 'scale' => 0, 'nullable' => false, 'columnName' => 'date_created', ));
		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
		$metadata->mapManyToOne(array( 'fieldName' => 'author', 'targetEntity' => 'Application\\DeskPRO\\Entity\\Person', 'mappedBy' => NULL, 'inversedBy' => NULL, 'joinColumns' => array( 0 => array( 'name' => 'author_id', 'referencedColumnName' => 'id', 'unique' => false, 'nullable' => true, 'onDelete' => 'set null', 'columnDefinition' => NULL, ), ),  ));
		$metadata->mapOneToMany(array( 'fieldName' => 'report_dashboard_stat', 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardStat', 'cascade' => array( 0 => 'remove', ), 'mappedBy' => 'report_dashboard',  ));
	}
}
