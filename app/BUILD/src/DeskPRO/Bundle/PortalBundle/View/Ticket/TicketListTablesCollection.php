<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
