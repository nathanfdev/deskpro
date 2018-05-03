<?php

/**
 * DeskPRO.
 *
 * @category DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\TicketLayout\LayoutFieldFilter;
use Application\DeskPRO\TicketLayout\TicketLayoutManager;
use Orb\Types\JsonObjectSerializer;

class TicketLayoutManagerService
{
    public static function create(DeskproContainer $container)
    {
        $filter = new LayoutFieldFilter(
            $container->getTicketFieldManager(),
            $container->getPersonFieldManager()
        );

        $ticket_layouts = array_map(function ($row) use ($filter) {
            $row['user_layout'] = JsonObjectSerializer::unserialize($row['user_layout']);
            $row['agent_layout'] = JsonObjectSerializer::unserialize($row['agent_layout']);

            $row['user_layout'] = $filter->filterInvalid($row['user_layout']);
            $row['agent_layout'] = $filter->filterInvalid($row['agent_layout']);

            return $row;
        }, $container->getDb()->fetchAll('
            SELECT department_id, user_layout, agent_layout
            FROM ticket_layouts
        '));

        $x = TicketLayoutManager::createWithLayoutArrays($ticket_layouts);

        return $x;
    }
}
