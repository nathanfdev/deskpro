<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Domain\DomainObject;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

/**
 * Report builder query.
 *
 * @property int $id
 * @property ReportBuilder $parent
 * @property string $title
 * @property string $description
 * @property string $query
 * @property bool $is_custom
 * @property string $category
 * @property int $display_order
 */
class ReportBuilder extends DomainObject
{
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
     * @var string
     */
    protected $query = '';

    /**
     * @var \Application\DeskPRO\Entity\ReportBuilder
     */
    protected $parent = null;

    /**
     * @var bool
     */
    protected $is_custom = true;

    /**
     * @var string|null
     */
    protected $category = null;

    /**
     * @var int
     */
    protected $display_order = 0;

    public function __construct()
    {
        $this->favorited_by = new ArrayCollection();
    }

    /**
     * @return ReportBuilder
     */
    public static function createReportBuilder()
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
        $this->setModelField('display_order', $display_order);

        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->setModelField('category',  $category);

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
        $this->setModelField('is_custom', (bool) $is_custom);

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
        $this->setModelField('query', $query);

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
        $this->setModelField('description', $description);

        return $this;
    }

    /**
     * @return ReportBuilder
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ReportBuilder $parent
     *
     * @return $this
     */
    public function setParent(ReportBuilder $parent)
    {
        $this->setModelField('parent', $parent);

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

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\ReportBuilder';
        $metadata->setPrimaryTable(
            [
                'name'              => 'report_builder',
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
                'fieldName'  => 'category',
                'type'       => 'string',
                'length'     => 25,
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => true,
                'columnName' => 'category',
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
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);

        $metadata->mapManyToOne(
            [
                'fieldName'    => 'parent',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\ReportBuilder',
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
