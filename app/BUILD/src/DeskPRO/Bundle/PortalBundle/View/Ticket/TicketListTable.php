<?php

namespace DeskPRO\Bundle\PortalBundle\View\Ticket;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\TicketsDataService;
use DeskPRO\Bundle\AppBundle\Model\TicketColumn;
use DeskPRO\Bundle\AppBundle\Model\TicketColumns;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class TicketListTable
{
    protected $ticketType;
    protected $ticketCategory;
    protected $ticketFilter;
    protected $page;
    protected $perPage;
    protected $pager;
    protected $pageName;
    protected $sortName;
    protected $sortDirectionName;
    protected $title;
    /**
     * @var TicketColumns
     */
    protected $columns;
    protected $activeColumns;
    protected $activeColumnsName;

    public function __construct($ticketCategory, $ticketType, $title, TicketColumns $columns, Request $request, $perPage)
    {
        $this->ticketCategory    = $ticketCategory;
        $this->ticketType        = $ticketType;
        $this->title             = $title;
        $this->pageName          = $ticketCategory.'_page';
        $this->sortName          = $ticketCategory.'_sort';
        $this->sortDirectionName = $ticketCategory.'_sort_direction';
        $this->activeColumnsName = $ticketCategory.'_cols';
        $this->ticketFilter      = null;
        $this->pager             = null;
        $this->activeColumns     = [];
        $this->columns           = $columns;
        $this->perPage           = $perPage;
        $this->makeFilterWithRequest($request);
    }

    /**
     * @return string
     */
    public function compileJsObj()
    {
        $js = "(function () {\n";
        $js .= "\treturn {\n";
        $js .= "\t\tcolumns: [\n";

        $fieldsJs = [];
        foreach ($this->columns->getColumns() as $column) {
            $bitJs = "\t\t\t{\n";
            $bitJs .= "\t\t\t\tid:                    '{$column->getId()}',\n";
            $bitJs .= "\t\t\t\tlabel:                  '{$column->getLabel()}',\n";
            $bitJs .= "\t\t\t\ttype:                  '{$column->getType()}',\n";
            $bitJs .= "\t\t\t\tactive:                  ".($this->isActive($column) ? 'true' : 'false')."\n";
            $bitJs .= "\t\t\t}";
            $fieldsJs[] = $bitJs;
        }

        $js .= implode(",\n", $fieldsJs)."\n\t\t],\n";

        $js .= "\t\tactive_columns: [\n";
        $js .= "\t\t\t'".implode("',\n\t\t\t'", $this->activeColumns)."'\n";
        $js .= "\t\t],\n";

        $js .= "\t\tactive_columns_param: '{$this->activeColumnsName}'\n";

        $js .= "\t}\n";

        $js .= '})()';

        return $js;
    }

    public function isActive(TicketColumn $column)
    {
        return in_array($column->getId(), $this->activeColumns);
    }

    protected function makeFilterWithRequest(Request $request, $per_page = 50)
    {
        $this->ticketFilter = new TicketFilter(
            $this->ticketType,
            $this->ticketCategory,
            $request->query->get($this->sortName, 'activity'),
            $request->query->get($this->sortDirectionName, 'desc'),
            $request->query->get('q')
        );
        $this->page          = $request->query->get($this->pageName, 1);
        $this->perPage       = $per_page;
        $this->activeColumns = explode(',', $request->query->get(
            $this->activeColumnsName,
            implode(',', $this->getDefaultColumnsIds())
        )); //comma seperated list of col ids (consts on this class)
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function makePagerUsingDataService(TicketsDataService $data_service, Person $person)
    {
        if (!$this->ticketFilter) {
            throw new RuntimeException('TicketListTable::makrPagerUsingDataService requires that a filter be present');
        }

        $this->pager = $data_service->getPager($person, $this->ticketFilter, $this->page, $this->perPage);

        return $this->pager;
    }

    public function getActiveColumns()
    {
        return $this->activeColumns;
    }

    public function getDefaultColumnsIds()
    {
        // get the initial columns to show by default

        if ($this->ticketType === TicketFilter::TYPE_ORGANIZATION) {
            return [
                TicketColumn::TYPE_SUBJECT,
                TicketColumn::TYPE_DEPARTMENT,
                TicketColumn::TYPE_USER,
                TicketColumn::TYPE_DATE_CREATED,
                TicketColumn::TYPE_DATE_ACTIVITY,
            ];
        }

        return [
            TicketColumn::TYPE_SUBJECT,
            TicketColumn::TYPE_DEPARTMENT,
            TicketColumn::TYPE_DATE_CREATED,
            TicketColumn::TYPE_DATE_ACTIVITY,
        ];
    }

    /**
     * @return mixed
     */
    public function getTicketType()
    {
        return $this->ticketType;
    }

    /**
     * @return mixed
     */
    public function getTicketCategory()
    {
        return $this->ticketCategory;
    }

    public function getTicketFilter()
    {
        return $this->ticketFilter;
    }

    /**
     * @return mixed
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @return mixed
     */
    public function getPerPage()
    {
        return $this->perPage;
    }

    public function getPager()
    {
        return $this->pager;
    }

    /**
     * @return string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @return string
     */
    public function getSortName()
    {
        return $this->sortName;
    }

    /**
     * @return string
     */
    public function getSortDirectionName()
    {
        return $this->sortDirectionName;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}
