<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\Domain\DomainObject;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * SavedDashboardWidget.
 */
class SavedDashboardWidget extends DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var null
     */
    protected $dashboard_widget = null;

    /**
     * @var SavedDashboardReport
     */
    protected $saved_report = null;

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
    protected $size = '8:5';

    /**
     * @var string
     */
    protected $type;

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var string
     */
    protected $options;

    /**
     * @var array already rendered in json data
     */
    protected $data;

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
     *
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
     *
     * @return $this
     */
    public function setPosition($position)
    {
        if (is_string($position) && false != /*that's right!*/
            strpos($position, ':')
        ) {
            $position = explode(':', $position);
        }

        if (is_array($position) && count($position) > 1) {
            $x = array_shift($position);
            $y = array_shift($position);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->position = implode(':', [(int) $x, (int) $y]);

        return $this;
    }

    /**
     * @return SavedDashboardReport
     */
    public function getSavedReport()
    {
        return $this->saved_report;
    }

    /**
     * @param SavedDashboardReport $saved_report
     *
     * @return $this
     */
    public function setSavedReport(SavedDashboardReport $saved_report)
    {
        $this->saved_report = $saved_report;

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
     *
     * @return $this
     */
    public function setSize($size)
    {
        if (is_string($size) && false != /*that's right!*/
            strpos($size, ':')
        ) {
            $size = explode(':', $size);
        }
        if (is_array($size) && count($size) > 1) {
            $x = array_shift($size);
            $y = array_shift($size);
        } else {
            throw new \Exception('Wrong point given!');
        }
        $this->size = implode(':', [(int) $x, (int) $y]);

        return $this;
    }

    /**
     * @return ReportDashboardWidget
     */
    public function getDashboardWidget()
    {
        return $this->dashboard_widget;
    }

    /**
     * @param ReportDashboardWidget $dashboard_widget
     *
     * @return $this
     */
    public function setDashboardWidget(ReportDashboardWidget $dashboard_widget = null)
    {
        $this->dashboard_widget = $dashboard_widget;

        return $this;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getVariables()
    {
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
     * @return string
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param string $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param array $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    /**
     * @param ClassMetadata $metadata
     *
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(
            [
                'name' => 'saved_dashboard_widget',
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
                'fieldName'  => 'position',
                'type'       => 'string',
                'length'     => 5,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'position',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'size',
                'type'       => 'string',
                'length'     => 5,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'size',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'type',
                'type'       => 'string',
                'length'     => 50,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'type',
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

        $metadata->mapField(
            [
                'fieldName'  => 'options',
                'type'       => 'text',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'options',
            ]
        );

        $metadata->mapField(
            [
                'fieldName'  => 'data',
                'type'       => 'json_array',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'data',
            ]
        );

        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'dashboard_widget',
                'targetEntity' => ReportWidget::class,
                'mappedBy'     => null,
                'inversedBy'   => null,
                'nullable'     => true,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'dashboard_widget_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'saved_report',
                'targetEntity' => SavedDashboardReport::class,
                'mappedBy'     => null,
                'inversedBy'   => 'saved_widgets',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'saved_report_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
    }
}
