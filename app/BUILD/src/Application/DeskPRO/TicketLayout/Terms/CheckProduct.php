<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout\Terms;

use Application\DeskPRO\Entity\Ticket;

/**
 * Class CheckProduct.
 */
class CheckProduct extends AbstractTicketLayoutTerm
{
    /**
     * {@inheritdoc}
     */
    public function isTicketMatch(Ticket $ticket)
    {
        $have_id  = $ticket->product ? $ticket->product->getId() : 0;
        $is_match = in_array($have_id, $this->options['product_ids']);

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
        foreach ((array) $this->options['product_ids'] as $id) {
            $js_ids[] = (int) $id;
        }
        $js_ids = '['.implode(',', $js_ids).']';
        $op     = $this->op == self::OP_NOT ? '===' : '!==';

        $js = <<<JS
function (ticket) { return $js_ids.indexOf(ticket.getProductId()) $op -1; }
JS;

        return $js;
    }
}
