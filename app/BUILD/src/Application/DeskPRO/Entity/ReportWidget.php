<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Report builder query.
 */
class ReportWidget extends DomainObject
{
    const LEGACY_RENDER_TYPE_BAR   = 'bar';
    const LEGACY_RENDER_TYPE_LINE  = 'line';
    const LEGACY_RENDER_TYPE_AREA  = 'area';
    const LEGACY_RENDER_TYPE_PIE   = 'pie';
    const LEGACY_RENDER_TYPE_TABLE = 'table';
    const LEGACY_RENDER_TYPE_STAT  = 'stat';

    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var string|null
     */
    protected $unique_key = null;

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $description = '';

    /**
     * @Assert\NotBlank()
     * @AppAssert\Reports\DpqlQuery()
     *
     * @var string
     */
    protected $query = '';

    /**
     * @var \Application\DeskPRO\Entity\ReportWidget
     */
    protected $parent = null;

    /**
     * @var bool
     */
    protected $is_custom = true;

    /**
     * @var array
     */
    protected $labels;

    /**
     * @var int
     */
    protected $display_order = 0;

    /**
     * @Assert\Count(min="1")
     *
     * @var array
     */
    protected $display_types = [];

    /**
     * @var array
     */
    protected $variables;

    /**
     * @var ArrayCollection
     */
    protected $favorited_by;

    /**
     * @var array
     */
    protected static $widgetGraphTypesMapping = [
        'simple_bars'  => self::LEGACY_RENDER_TYPE_BAR,
        'bars'         => self::LEGACY_RENDER_TYPE_BAR,
        'simple_lines' => self::LEGACY_RENDER_TYPE_LINE,
        'lines'        => self::LEGACY_RENDER_TYPE_LINE,
        'area'         => self::LEGACY_RENDER_TYPE_AREA,
        'simple_area'  => self::LEGACY_RENDER_TYPE_AREA,
        'pie'          => self::LEGACY_RENDER_TYPE_PIE,
        'table'        => self::LEGACY_RENDER_TYPE_TABLE,
        'simple_stat'  => self::LEGACY_RENDER_TYPE_STAT,
    ];

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->favorited_by = new ArrayCollection();
    }

    /**
     * @return ReportWidget
     */
    public static function createReportWidget()
    {
        return new self();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getUniqueKey()
    {
        return $this->unique_key;
    }

    /**
     * @param null|string $unique_key
     *
     * @return $this
     */
    public function setUniqueKey($unique_key)
    {
        $this->setModelField('unique_key', $unique_key);

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param string $query
     *
     * @return $this
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @return ReportWidget
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ReportWidget $parent
     *
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return $this->is_custom;
    }

    /**
     * @param bool $is_custom
     *
     * @return $this
     */
    public function setIsCustom($is_custom)
    {
        $this->is_custom = $is_custom;

        return $this;
    }

    /**
     * @return int
     */
    public function getDisplayOrder()
    {
        return $this->display_order;
    }

    /**
     * @param int $display_order
     *
     * @return $this
     */
    public function setDisplayOrder($display_order)
    {
        $this->display_order = $display_order;

        return $this;
    }

    /**
     * @param string $type
     * @param array  $params
     *
     * @return mixed|string
     */
    public function getTitle($type = 'raw', $params = [])
    {
        if ($type == 'raw') {
            return $this->title;
        } elseif ($type == 'no_groupable') {
            return preg_replace('/\[(.+?)\]/', '$1',  $this->title);
        }

        $repository  = $this->getRepository();
        $groupParams = $repository->getReportGroupParams();

        if (!is_array($params)) {
            $newParams = [];
            foreach ($params ? explode(',', $params) : [] as $k => $v) {
                $newParams[$k + 1] = $v;
            }
            $params = $newParams;
        }

        $title = $this->title;

        if ($type != 'groupable') {
            $title = preg_replace('/\[(.+?)\]/', '$1', $title);
        }

        $getDefault = function ($extras, array $paramSet, $component = false) use ($type) {
            if ($type == 'placeholder') {
                return false;
            }

            /*if (preg_match('#,\s*default:\s*([^,]+)\s*#', $extras, $match)) {
                if ($component) {
                    if (isset($paramSet[$component][$match[1]])) {
                        return $paramSet[$component][$match[1]];
                    }
                } elseif (isset($paramSet[$match[1]])) {
                    return $paramSet[$match[1]];
                }
            }*/

            if ($component && is_array($paramSet[$component])) {
                return reset($paramSet[$component]);
            } else {
                return reset($paramSet);
            }
        };

        $placeholderTitle = $title;

        $title = preg_replace_callback('/<(\d+):(date group)([^>]*)>/', function ($match) use ($params, $groupParams, $getDefault) {
            $id = $match[1];
            if (isset($params[$id]) && isset($groupParams['dates'][$params[$id]])) {
                return $groupParams['dates'][$params[$id]][0];
            }

            $default = $getDefault($match[3], $groupParams['dates']);
            if ($default) {
                return $default[0];
            }

            return '<date>';
        }, $title);

        $title = preg_replace_callback('/<(\d+):(field group):([a-zA-Z0-9_]+)([^>]*)>/', function ($match) use ($params, $groupParams, $getDefault) {
            $id = $match[1];
            $type = $match[3];
            if (isset($params[$id]) && isset($groupParams['fields'][$type][$params[$id]])) {
                return $groupParams['fields'][$type][$params[$id]][0];
            }

            $default = $getDefault($match[4], $groupParams['fields'], $type);
            if ($default) {
                return $default[0];
            }

            return '<field>';
        }, $title);

        $title = preg_replace_callback('/<(\d+):(status group):([a-zA-Z0-9_]+)([^>]*)>/', function ($match) use ($params, $groupParams, $getDefault) {
            $id = $match[1];
            $type = $match[3];
            if (isset($params[$id]) && isset($groupParams['statuses'][$type][$params[$id]])) {
                return $groupParams['statuses'][$type][$params[$id]][0];
            }

            $default = $getDefault($match[4], $groupParams['statuses'], $type);
            if ($default) {
                return $default[0];
            }

            return '<status>';
        }, $title);

        $title = preg_replace_callback('/<(\d+):(order group):([a-zA-Z0-9_]+)([^>]*)>/', function ($match) use ($params, $groupParams, $getDefault) {
            $id = $match[1];
            $type = $match[3];
            if (isset($params[$id]) && isset($groupParams['orders'][$type][$params[$id]])) {
                return $groupParams['orders'][$type][$params[$id]][0];
            }

            $default = $getDefault($match[4], $groupParams['orders'], $type);
            if ($default) {
                return $default[0];
            }

            return '<order>';
        }, $title);

        $title = preg_replace('/<chart:[a-zA-Z0-9_-]+>/', '', $title);

        if ($placeholderTitle != $title) {
            $title = preg_replace_callback('/(, )?(split by|grouped by) ([a-zA-Z0-9 ]+) & ([a-zA-Z0-9]+)/', function ($match) {
                $firstMatch = rtrim($match[3]);
                $secondMatch = rtrim($match[4]);

                if ($firstMatch == 'nothing' && $secondMatch == 'nothing') {
                    // double group/splt on nothing - remove whole string
                    return '';
                } elseif ($firstMatch == 'nothing') {
                    // first group is nothing, but second on something
                    return $match[1].$match[2].' '.$match[4];
                } elseif ($secondMatch == 'nothing') {
                    // first group is something, but second on nothing
                    return $match[1].$match[2].' '.$match[3];
                }

                return $match[0];
            }, $title);

            $title = preg_replace('/(, )?(split by|grouped by) nothing/', '', $title);
        }

        return $title;
    }

    /**
     * @param $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }
    /**
     * Gets the DPQL parts for this report's query.
     *
     * @return array
     */
    public function getParts()
    {
        $compiler  = new \Application\DeskPRO\Dpql\Compiler();
        $statement = $compiler->compile($this->query);

        return $statement->getDpqlParts();
    }

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->is_custom || App::getConfig('debug.dev');
    }

    /**
     * @return int
     */
    public function hasPlaceholders()
    {
        return preg_match('/<\d+:[^>]+>/', $this->title);
    }

    /**
     * Determines if the passed string is effectively different.
     * The string may be slightly different and still pass.
     *
     * @param string $query
     *
     * @return bool
     */
    public function isQueryDifferent($query)
    {
        $query     = preg_replace('/\s/', '', $query);
        $thisQuery = preg_replace('/\s/', '', $this->query);

        return $query != $thisQuery;
    }

    /**
     * Quick lookup handler to determine if a particular user has favorited this.
     *
     * @var array
     */
    protected $_is_favorited = [];

    /**
     * Returns true if the specified person has favorited this.
     *
     * @param Person|null $person Defaults to current person
     *
     * @return bool
     */
    public function isFavorited(Person $person = null)
    {
        return false;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     *
     * @return $this
     */
    public function setLabels(array $labels = null)
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * @return array
     */
    public function getDisplayTypes()
    {
        return $this->display_types;
    }

    /**
     * @return string[]
     */
    public function getGraphTypes()
    {
        $graphTypes = [];
        foreach ($this->display_types as $displayType) {
            $graphTypes[] = self::getGraphType($displayType);
        }

        return $graphTypes;
    }

    /**
     * @param $displayType
     *
     * @return string
     */
    public static function getGraphType($displayType)
    {
        return isset(self::$widgetGraphTypesMapping[$displayType])
            ? self::$widgetGraphTypesMapping[$displayType]
            : self::LEGACY_RENDER_TYPE_TABLE;
    }

    /**
     * @param array $display_types
     *
     * @return $this
     */
    public function setDisplayTypes($display_types)
    {
        $this->display_types = $display_types;

        return $this;
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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = \Application\DeskPRO\EntityRepository\ReportWidget::class;
        $metadata->setPrimaryTable(
            [
                 'name'    => 'report_widget',
                 'indexes' => [
                     'parent_id_idx' => ['columns' => ['parent_id']],
                 ],
                 'uniqueConstraints' => [
                     'unique_key_idx' => ['columns' => ['unique_key']],
                 ],
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
                 'fieldName'  => 'unique_key',
                 'type'       => 'string',
                 'length'     => 50,
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => true,
                 'columnName' => 'unique_key',
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
                 'fieldName'  => 'description',
                 'type'       => 'text',
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'description',
            ]
        );
        $metadata->mapField(
            [
                 'fieldName'  => 'query',
                 'type'       => 'text',
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'query',
            ]
        );
        $metadata->mapField(
            [
                 'fieldName'  => 'is_custom',
                 'type'       => 'boolean',
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'is_custom',
            ]
        );
        $metadata->mapField(
            [
                 'fieldName'  => 'labels',
                 'type'       => 'simple_array',
                 'length'     => 255,
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => true,
                 'columnName' => 'labels',
            ]
        );
        $metadata->mapField(
            [
                 'fieldName'  => 'display_order',
                 'type'       => 'integer',
                 'precision'  => 0,
                 'scale'      => 0,
                 'nullable'   => false,
                 'columnName' => 'display_order',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'display_types',
                'type'       => 'simple_array',
                'nullable'   => false,
                'columnName' => 'display_types',
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'variables',
                'type'       => 'json_array',
                'nullable'   => true,
                'columnName' => 'variables',
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToOne(
            [
                 'fieldName'    => 'parent',
                 'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportWidget',
                 'mappedBy'     => null,
                 'inversedBy'   => null,
                 'joinColumns'  => [
                     0 => [
                         'name'                 => 'parent_id',
                         'referencedColumnName' => 'id',
                         'nullable'             => true,
                         'onDelete'             => 'set null',
                         'columnDefinition'     => null,
                     ],
                 ],
            ]
        );
    }
}
