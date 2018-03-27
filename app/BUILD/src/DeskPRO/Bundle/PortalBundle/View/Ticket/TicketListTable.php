<?php

namespace DeskPRO\Bundle\PortalBundle\View\Ticket;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\DataService\TicketsDataService;
use DeskPRO\Bundle\AppBundle\Model\TicketColumn;
use DeskPRO\Bundle\AppBundle\Model\TicketColumns;
use DeskPRO\Bundle\PortalBundle\Model\TicketFilter;
use Symfony\Component\HttpFoundation\Request;

class TicketListTable
{
    protected $ticket_type;
    protected $ticket_category;
    protected $ticket_filter;
    protected $page;
    protected $per_page;
    protected $pager;
    protected $page_name;
    protected $sort_name;
    protected $sort_direction_name;
    protected $title;
    /**
     * @var TicketColumns
     */
    protected $columns;
    protected $active_columns_name;

    public function __construct($ticket_category, $ticket_type, $title, TicketColumns $columns, Request $request, $per_page)
    {
        $this->ticket_category     = $ticket_category;
        $this->ticket_type         = $ticket_type;
        $this->title               = $title;
        $this->page_name           = $ticket_category.'_page';
        $this->sort_name           = $ticket_category.'_sort';
        $this->sort_direction_name = $ticket_category.'_sort_direction';
        $this->active_columns_name = $ticket_category.'_cols';
        $this->ticket_filter       = null;
        $this->pager               = null;
        $this->active_columns      = [];
        $this->columns             = $columns;
        $this->per_page            = $per_page;
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

        $fields_js = [];
        foreach ($this->columns->getColumns() as $column) {
            $bit_js = "\t\t\t{\n";
            $bit_js .= "\t\t\t\tid:                    '{$column->getId()}',\n";
            $bit_js .= "\t\t\t\tlabel:                  '{$column->getLabel()}',\n";
            $bit_js .= "\t\t\t\ttype:                  '{$column->getType()}',\n";
            $bit_js .= "\t\t\t\tactive:                  ".($this->isActive($column) ? 'true' : 'false')."\n";
            $bit_js .= "\t\t\t}";
            $fields_js[] = $bit_js;
        }

        $js .= implode(",\n", $fields_js)."\n\t\t],\n";

        $js .= "\t\tactive_columns: [\n";
        $js .= "\t\t\t'".implode("',\n\t\t\t'", $this->active_columns)."'\n";
        $js .= "\t\t],\n";

        $js .= "\t\tactive_columns_param: '{$this->active_columns_name}'\n";

        $js .= "\t}\n";

        $js .= '})()';

        return $js;
    }

    public function isActive(TicketColumn $column)
    {
        return in_array($column->getId(), $this->active_columns);
    }

    protected function makeFilterWithRequest(Request $request, $per_page = 50)
    {
        $this->ticket_filter = new TicketFilter(
            $this->ticket_type,
            $this->ticket_category,
            $request->query->get($this->sort_name, 'activity'),
            $request->query->get($this->sort_direction_name, 'desc'),
            $request->query->get('q')
        );
        $this->page           = $request->query->get($this->page_name, 1);
        $this->per_page       = $per_page;
        $this->active_columns = explode(',', $request->query->get(
            $this->active_columns_name,
            implode(',', $this->getDefaultColumnsIds())
        )); //comma seperated list of col ids (consts on this class)
    }

    public function getColumns()
    {
        return $this->columns;
    }

    public function makePagerUsingDataService(TicketsDataService $data_service, Person $person)
    {
        if (!$this->ticket_filter) {
            throw new \RuntimeException('TicketListTable::makrPagerUsingDataService requires that a filter be present');
        }

        $this->pager = $data_service->getPager($person, $this->ticket_filter, $this->page, $this->per_page);

        return $this->pager;
    }

    public function getActiveColumns()
    {
        return $this->active_columns;
    }

    public function getDefaultColumnsIds()
    {
        // get the initial columns to show by default

        if ($this->ticket_type === TicketFilter::TYPE_ORGANIZATION) {
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
        return $this->ticket_type;
    }

    /**
     * @return mixed
     */
    public function getTicketCategory()
    {
        return $this->ticket_category;
    }

    public function getTicketFilter()
    {
        return $this->ticket_filter;
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
        return $this->per_page;
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
        return $this->page_name;
    }

    /**
     * @return string
     */
    public function getSortName()
    {
        return $this->sort_name;
    }

    /**
     * @return string
     */
    public function getSortDirectionName()
    {
        return $this->sort_direction_name;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}
