<?php

namespace DeskPRO\Bundle\ReportBundle\Reports;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ReportBundle\Dpql2\DpqlContext;
use DeskPRO\Component\Util\ListUtils;

/**
 * Handler for defining how DPQL results should be formatted, including
 * how grouping is done and how the columns should be ordered/displayed.
 */
class ResultMetadata
{
    const FLAG_HIERARCHICAL            = 1;
    const FLAG_WITH_ROLLUP             = 2;
    const FLAG_HIERARCHY_DESCENDS_FROM = 3;
    const FLAG_LAYERED                 = 4;

    /**
     * List of columns that should be selected.
     *
     * Array needs these keys:
     *  - title: Title of the column
     *  - resultId: Column number from the SQL results used to display
     *  - renderer: Null for default behavior, or a closure to control how to render the column
     *
     * Grouping arrays need these keys as well:
     *  - groupResultId: Column number from SQL results that's used to group
     *
     * @var array[int]
     */
    private $columns = [];

    /**
     * List of columns that will be grouped on in the X direction.
     * That is, this will be listed as headers from left to right.
     * The use of X grouping causes the results to be a matrix table.
     *
     * See columns for array structure.
     *
     * @var array[int]
     */
    private $groupXColumns = [];

    /**
     * List of columns that will be grouped on in the Y direction.
     * That is, this displays like MySQL's native GROUP BY.
     *
     * See columns for array structure.
     *
     * @var array[int]
     */
    private $groupYColumns = [];

    /**
     * @var array
     */
    private $groupStackColumns = [];

    /**
     * List of columns that will be used to split the results into
     * separate tables.
     *
     * See columns for array structure, but without title.
     *
     * @var array[int]
     */
    private $splitColumns = [];

    /**
     * List of select column IDs that should be totaled.
     *
     * @var array
     */
    private $totalColumns = [];

    /**
     * @var array
     */
    private $flags = [];

    /**
     * @var DpqlContext
     */
    private $context;

    /**
     * Constructor.
     *
     * @param DpqlContext $context
     */
    public function __construct(DpqlContext $context = null)
    {
        $this->context = $context;
    }

    /**
     * Adds a column that will be selected/output into the results.
     *
     * @param string        $title
     * @param int|int[]     $resultId
     * @param \Closure|null $renderer
     */
    public function addSelectColumn($title, $resultId, $renderer = null)
    {
        $this->columns[] = [
            'title'    => $title,
            'resultId' => $resultId,
            'renderer' => $renderer,
        ];
    }

    /**
     * Gets the columns that will be selected/output.
     *
     * @return array
     */
    public function getSelectColumns()
    {
        return $this->columns;
    }

    /**
     * Adds a column that will be grouped in the Y direction (normal
     * MySQL group by). Note that these columns should be selected/displayed
     * before the normal select columns.
     *
     * @param string        $title
     * @param int           $groupResultId The ID of the column in the results that holds the grouping field value
     * @param int           $resultId
     * @param \Closure|null $renderer
     */
    public function addGroupYColumn($title, $groupResultId, $resultId, $renderer = null)
    {
        $this->groupYColumns[] = [
            'title'         => $title,
            'groupResultId' => $groupResultId,
            'resultId'      => $resultId,
            'renderer'      => $renderer,
        ];
    }

    /**
     * Gets columns that will be used for Y grouping.
     *
     * @return array
     */
    public function getGroupYColumns()
    {
        return $this->groupYColumns;
    }

    /**
     * Adds a column that will be grouped in the X direction to create
     * a matrix table.
     *
     * @param string        $title
     * @param int|string    $groupResultId The ID of the column in the results that holds the grouping field value
     * @param int           $resultId
     * @param \Closure|null $renderer
     */
    public function addGroupXColumn($title, $groupResultId, $resultId, $renderer = null)
    {
        $this->groupXColumns[] = [
            'title'         => $title,
            'groupResultId' => $groupResultId,
            'resultId'      => $resultId,
            'renderer'      => $renderer,
        ];
    }

    /**
     * Gets columns to be grouped in the X direction to create a matrix table.
     *
     * @return array
     */
    public function getGroupXColumns()
    {
        return $this->groupXColumns;
    }

    /**
     * @param $groupId
     * @param $printId
     */
    public function addGroupStackColumn($groupId, $printId)
    {
        $this->groupStackColumns[] = [
            'groupId' => $groupId,
            'printId' => $printId,
        ];
    }

    /**
     * @return array
     */
    public function getGroupStackColumns()
    {
        return $this->groupStackColumns;
    }

    /**
     * Adds a column that will be used for splitting into separate tables.
     *
     * @param int           $resultId
     * @param \Closure|null $renderer
     */
    public function addSplitColumn($resultId, $renderer = null)
    {
        $this->splitColumns[] = [
            'resultId' => $resultId,
            'renderer' => $renderer,
        ];
    }

    /**
     * Gets columns that are to be used to split results into tables.
     *
     * @return array
     */
    public function getSplitColumns()
    {
        return $this->splitColumns;
    }

    /**
     * @param int $id
     */
    public function addTotalColumn($id)
    {
        $this->totalColumns[$id] = $id;
    }

    /**
     * @return array
     */
    public function getTotalColumns()
    {
        return $this->totalColumns;
    }

    /**
     * @param int $flag
     *
     * @throws \Exception
     */
    public function addFlag($flag)
    {
        $this->flags[] = $flag;

        if ($this->hasFlag(self::FLAG_WITH_ROLLUP) && $this->hasFlag(self::FLAG_HIERARCHY_DESCENDS_FROM)) {
            throw new \Exception('You cannot use WITH ROLLUP and HIERARCHY_DESCENDS_FROM() together');
        }
    }

    /**
     * @param int $flag
     */
    public function removeFlag($flag)
    {
        if ($this->hasFlag($flag)) {
            $this->flags = ListUtils::filterOutValues($this->flags, $flag);
        }
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function hasFlag($flag)
    {
        return in_array($flag, $this->flags);
    }

    /**
     * @return DpqlContext
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return Person|null
     */
    public function getPerson()
    {
        return $this->context ? $this->context->getPerson() : null;
    }
}
