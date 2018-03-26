<?php

/**
 * DeskPRO.
 *
 * @category TicketLayout
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\Entity\Ticket;

class LayoutDisplay extends Layout implements \Countable, \IteratorAggregate
{
    const NEW_TICKET  = 'new_ticket';
    const VIEW_TICKET = 'view_ticket';
    const EDIT_TICKET = 'edit_ticket';

    /**
     * @var string
     */
    private $mode;

    /**
     * @var Ticket|null
     */
    private $ticket_context;

    /**
     * @param Layout $layout
     * @param        $mode
     * @param Ticket $ticket_context
     *
     * @return LayoutDisplay
     */
    public static function createFromLayout(Layout $layout, $mode, Ticket $ticket_context = null)
    {
        $obj = new self($mode, $ticket_context);

        if ($ticket_context) {
            foreach ($layout->all() as $f) {
                if ($f->hasCriteria()) {
                    if ($f->getCriteria()->isTicketMatch($ticket_context)) {
                        $obj->add($f);
                    }
                } else {
                    $obj->add($f);
                }
            }
        } else {
            foreach ($layout->all() as $f) {
                $obj->add($f);
            }
        }

        return $obj;
    }

    /**
     * @param        $mode
     * @param Ticket $ticket_context
     */
    public function __construct($mode, Ticket $ticket_context = null)
    {
        $this->mode           = $mode;
        $this->ticket_context = $ticket_context;
    }

    /**
     * @param LayoutField $field
     * @param null        $before_field
     *
     * @return bool|void
     */
    public function add(LayoutField $field, $before_field = null)
    {
        switch ($field->getFieldType()) {
            case 'department':
            case 'subject':
            case 'message':
            case 'person':
                break;

            default:
                switch ($this->mode) {
                    case self::NEW_TICKET:
                        if (!$field->isVisibleOnNew()) {
                            return false;
                        }
                        break;

                    case self::VIEW_TICKET:
                        if (!$field->isVisibleOnView()) {
                            return false;
                        }
                        if (!$field->isVisibleOnViewAlways() && $this->ticket_context) {
                            if (!self::checkTicketHasField($field, $this->ticket_context)) {
                                return false;
                            }
                        }
                        break;

                    case self::EDIT_TICKET:
                        if (!$field->isVisibleOnEdit()) {
                            return false;
                        }
                        break;
                }
        }

        parent::add($field, $before_field);

        return true;
    }

    /**
     * @param LayoutField $field
     * @param Ticket      $ticket
     *
     * @return bool
     */
    public static function checkTicketHasField(LayoutField $field, Ticket $ticket)
    {
        switch ($field->getFieldType()) {
            case 'product':
                return $ticket->product ? true : false;

            case 'category':
                return $ticket->category ? true : false;

            case 'priority':
                return $ticket->priority ? true : false;

            case 'workflow':
                return $ticket->workflow ? true : false;

            case 'ticket_field':
                return $ticket->hasCustomField($field->getFieldId());

            case 'user_field':
                return $ticket->person->hasCustomField($field->getFieldId());
            case 'custom_field':
                return true;
        }
    }

    public function getTicket()
    {
        return $this->ticket_context;
    }
}
