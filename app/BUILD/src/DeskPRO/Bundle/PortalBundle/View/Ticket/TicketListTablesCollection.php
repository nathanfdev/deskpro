<?php

namespace DeskPRO\Bundle\PortalBundle\View\Ticket;

use Orb\Util\Strings;

/**
 * A fast way to represent multiple tables. It makes unique IDs for them, and can produce a JS compilation of them.
 */
class TicketListTablesCollection implements \IteratorAggregate
{
    protected $tables;

    public function __construct()
    {
        $this->tables  = [];
        $this->next_id = 0;
    }

    public function addTable(TicketListTable $table)
    {
        $this->tables[$this->next_id] = $table;
        ++$this->next_id;
    }

    public function compileJsObj()
    {
        $js = "(function () {\n";
        $js .= "\treturn {\n";

        $layout_codes = [];
        foreach ($this->tables as $k => $table) {
            $k_str = "'$k'";

            $code           = trim(Strings::modifyLines($table->compileJsObj(), "\t\t\t"));
            $bit_js         = "\t\t{$k_str}: {$code}";
            $layout_codes[] = $bit_js;
        }

        $js .= implode(",\n", $layout_codes)."\n";
        $js .= '}'."\n";
        $js .= '})()';

        return $js;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->tables);
    }
}
