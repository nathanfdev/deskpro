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

/**
 * ReportDashboardWidget
 *
 * @property int             $id
 * @property string          $title
 * @property ReportBuilder   $report
 * @property string          $position simple 1:1 position just explode to own x and y (col & row)
 * @property string          $size     same as previous, explode and you'll have sizeX and sizeY
 * @property string          $hc_data  hardcoded data for default dashboards
 */
class ReportDashboardWidget extends DomainObject
{
	/**
	 * @var int
	 */
	protected $id = null;

    /**
     * @var ReportDashboardReport
     */
    protected $report = null;

    /**
     * @var ReportBuilder it's a reference to ReportBuilder Entity that holds DPQL
     */
    protected $widget = null;

	/**
	 * @var string
	 */
	protected $title = '';

    /**
     * @var string
     */
    protected $position = '0:0';

    /**
     * @var string
     */
    protected $size = '1:1';

    /**
     * @var string
     */
    protected $hc_data = null;

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
        $this->id = (int) $id;
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
     * @return array
     */
    public function getPosition()
    {
        return explode(':', $this->position);
    }

    /**
     * @param string|array $position
     *
     * @throws \Exception
     * @return $this
     */
    public function setPosition($position)
    {

        if (is_string($position) && false != /*that's right!*/  strpos($position, ":")) {
            $position = explode(":", $position);
        }

        if(is_array($position) && count($position) > 1) {
            $x = array_shift($position);
            $y = array_shift($position);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->position = implode(":", array((int) $x, (int)$y));
        return $this;
    }

    /**
     * @return ReportDashboardReport
     */
    public function getReport()
    {
        return $this->report;
    }

    /**
     * @param ReportDashboardReport $report
     *
     * @return $this
     */
    public function setReport(ReportDashboardReport $report)
    {
        $this->report = $report;
        return $this;
    }

    /**
     * @return array
     */
    public function getSize()
    {
        return explode(':', $this->size);
    }

    /**
     * @param string $size
     *
     * @throws \Exception
     * @return $this
     */
    public function setSize($size)
    {
        if (is_string($size) && false != /*that's right!*/  strpos($size, ":")) {
            $size = explode(":", $size);
        }
        if(is_array($size) && count($size) > 1) {
            $x = array_shift($size);
            $y = array_shift($size);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->size = implode(":", array((int) $x, (int)$y));
        return $this;
    }

    /**
     * @return ReportBuilder
     */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * @param ReportBuilder $report
     *
     * @return $this
     */
    public function setWidget(ReportBuilder $report = null)
    {
        $this->widget = $report;
        return $this;
    }

    /**
     * @return string
     */
    public function getHcData()
    {
        if(!is_null($this->hc_data) && !is_array($this->hc_data)) {
            $temp = explode(':', $this->hc_data);
            $this->hc_data = array(
                'inner_type' => $temp[1],
                'outer_type' => $temp[0],
            );
        }
        return $this->hc_data;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function setHcData($data)
    {
        $this->hc_data = $data;
        return $this;
    }


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
//		$metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ReportBuilder';
        $metadata->setPrimaryTable(
            array(
                'name'    => 'report_dashboard_widget',
                'indexes' => array(
                    'report_id_idx' => array('columns' => array('report_id')),
                    'widget_id_idx'    => array('columns' => array('widget_id'))
                ),
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
				 'fieldName'  => 'position',
				 'type'       => 'string',
				 'length'     => 5,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'position',
			)
		);
        $metadata->mapField(
			array(
				 'fieldName'  => 'size',
				 'type'       => 'string',
				 'length'     => 5,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => false,
				 'columnName' => 'size',
			)
		);
        $metadata->mapField(
			array(
				 'fieldName'  => 'hc_data',
				 'type'       => 'string',
				 'length'     => 50,
				 'precision'  => 0,
				 'scale'      => 0,
				 'nullable'   => true,
				 'columnName' => 'hc_data',
			)
		);

		$metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

		$metadata->mapManyToOne(
			array(
				 'fieldName'    => 'widget',
				 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportBuilder',
				 'mappedBy'     => null,
				 'inversedBy'   => null,
				 'joinColumns'  => array(
					 0 => array(
						 'name'                 => 'widget_id',
						 'referencedColumnName' => 'id',
						 'nullable'             => true,
						 'onDelete'             => 'cascade',
						 'columnDefinition'     => null,
					 ),
				 ),
			)
		);

        $metadata->mapManyToOne(
			array(
				 'fieldName'    => 'report',
				 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportDashboardReport',
				 'mappedBy'     => null,
				 'inversedBy'   => 'widgets',
				 'joinColumns'  => array(
					 0 => array(
						 'name'                 => 'report_id',
						 'referencedColumnName' => 'id',
						 'nullable'             => false,
						 'onDelete'             => 'cascade',
						 'columnDefinition'     => null,
					 ),
				 ),
			)
		);
	}
}
