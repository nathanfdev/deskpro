<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;

class LayoutFieldFilter
{
    /**
     * @var \Application\DeskPRO\CustomFields\TicketFieldManager
     */
    private $ticket_fields;

    /**
     * @var \Application\DeskPRO\CustomFields\PersonFieldManager
     */
    private $user_fields;

    /**
     * @param TicketFieldManager $ticket_fields
     * @param PersonFieldManager $user_fields
     */
    public function __construct(TicketFieldManager $ticket_fields, PersonFieldManager $user_fields)
    {
        $this->ticket_fields = $ticket_fields;
        $this->user_fields   = $user_fields;
    }

    /**
     * @param LayoutField $field
     *
     * @return bool
     */
    public function isFieldValid(LayoutField $field)
    {
        switch ($field->getFieldType()) {
            case 'ticket_field':
                if (!$this->ticket_fields->getFieldFromId($field->getFieldId())) {
                    return false;
                }
                break;

            case 'user_field':
                if (!$this->user_fields->getFieldFromId($field->getFieldId())) {
                    return false;
                }
                break;

            case 'category':
                return $this->ticket_fields->isCategoryEnabled();
            case 'priority':
                return $this->ticket_fields->isPriorityEnabled();
            case 'workflow':
                return $this->ticket_fields->isWorkflowEnabled();
            case 'product':
                return $this->ticket_fields->isProductEnabled();
        }

        return true;
    }

    /**
     * @param Layout $layout
     *
     * @return array
     */
    public function getInvalidIds(Layout $layout)
    {
        $invalid = [];
        foreach ($layout as $f) {
            if (!$this->isFieldValid($f)) {
                $invalid[] = $f->getId();
            }
        }

        return $invalid;
    }

    /**
     * @param Layout $layout
     *
     * @return Layout
     */
    public function filterInvalid(Layout $layout)
    {
        foreach ($this->getInvalidIds($layout) as $id) {
            $layout->remove($id);
        }

        return $layout;
    }
}
