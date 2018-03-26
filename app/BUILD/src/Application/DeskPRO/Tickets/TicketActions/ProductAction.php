<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;

class ProductAction extends AbstractAction implements PermissionableAction
{
    /** @var int */
    protected $product_id;

    public function __construct($product)
    {
        $this->product_id = $product;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $ticket['product_id'] = $this->product_id;
    }

    /**
     * {@inheritdoc}
     */
    public function checkPermission(Ticket $ticket, Person $person)
    {
        if ($ticket->getProductId() == $this->product_id) {
            return true;
        }

        if (!$person->PermissionsManager->TicketChecker->canModify($ticket, 'fields')) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        if ($ticket['product_id'] == $this->product_id) {
            return [];
        }

        return [
            ['action' => 'product', 'product_id' => $this->product_id],
        ];
    }

    /**
     * Get the product id.
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->product_id;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        return $otherAction;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        $tr = App::getTranslator();

        if ($this->product_id == 0) {
            return $tr->phrase('agent.tickets.remove_product_action');
        } else {
            $names = App::getEntityRepository('DeskPRO:Product')->getFullNames();
            if (!isset($names[$this->product_id])) {
                $name = "<error>Unknown #{$this->product_id}</error>";
            } else {
                $name = $names[$this->product_id];
            }

            return $tr->phrase('agent.tickets.set_product_action', ['product' => $name]);
        }
    }
}
