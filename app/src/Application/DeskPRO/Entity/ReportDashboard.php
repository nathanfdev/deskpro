<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Report builder query
 *
 * @property int $id
 * @property string $title
 */
class ReportDashboard extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

	/**
	 * @var string
	 */
	protected $title = '';

    /**
     * @var ArrayCollection
     */
    protected $reports;

    /**
     * @var boolean
     * In case that default reports are comes only with upgrade script we have only getter here
     * @see ReportDashboard::isDefault()
     */
    protected $is_default;

    public function __construct()
    {
        $this->is_default = false;
        $this->reports = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param integer $id
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
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }


    /**
     * @return ReportDashboardReport[]
     */
    public function getReports()
    {
        return $this->reports;
    }


    /**
     * @param ReportDashboardReport $report
     *
     * @return $this
     */
    public function addReport(ReportDashboardReport $report)
    {
        $this->reports->add($report);
        $report->setDashboard($this);
        return $this;
    }

    /**
     * @return bool
     */
    public function isDefault()
    {
        return (bool) $this->is_default;
    }

    public function setDefault($default)
    {
        $this->is_default = (bool) $default;
        return $this;
    }


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
		$metadata->setPrimaryTable(
			array(
                'name' => 'report_dashboard',
            )
		);
		$metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_DEFERRED_IMPLICIT);
		$metadata->mapField(
			array(
				 'fieldName'  => 'id',
				 'type'       => 'integer',
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'id',
				 'id'         => true,
            )
		);
		$metadata->mapField(
			array(
				 'fieldName'  => 'title',
				 'type'       => 'string',
				 'length'     => 255,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'title',
            )
		);

        $metadata->mapField(
            array(
                'fieldName'  => 'is_default',
                'default'    => false,
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'is_default',
            )
        );

        $metadata->mapOneToMany(
            array(
            'fieldName'    => 'reports',
            'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardReport',
            'mappedBy'     => 'dashboard',
            'inversedBy'   => null,
            'orderBy'      => array('sort_order'=>'ASC', 'id'=>'ASC'),
            'cascade'      => array('persist', 'remove'),
            ));

		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
	}
}
