<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;

class CheckCategory extends AbstractTicketLayoutTerm
{
    /**
     * {@inheritdoc}
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $have_id  = $ticket->category ? $ticket->category->getId() : 0;
        $is_match = in_array($have_id, $this->options['category_ids']);

        if ($this->op == self::OP_NOT) {
            $is_match = !$is_match;
        }

        return $is_match;
    }

    /**
     * {@inheritdoc}
     */
    public function compileJsCheck()
    {
        $js_ids = [];
        foreach ((array) $this->options['category_ids'] as $id) {
            $js_ids[] = (int) $id;
        }
        $js_ids = '['.implode(',', $js_ids).']';
        $op     = $this->op == self::OP_NOT ? '===' : '!==';

        $js = <<<JS
function (ticket) { return $js_ids.indexOf(ticket.getCategoryId()) $op -1; }
JS;

        return $js;
    }
}
