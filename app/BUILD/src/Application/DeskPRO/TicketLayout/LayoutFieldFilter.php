<?php

namespace Application\DeskPRO\TicketLayout;

use Application\DeskPRO\CustomFields\OrganizationFieldManager;
use Application\DeskPRO\CustomFields\PersonFieldManager;
use Application\DeskPRO\CustomFields\TicketFieldManager;

/**
 * Class LayoutFieldFilter.
 */
class LayoutFieldFilter
{
    /**
     * @var TicketFieldManager
     */
    private $ticketFields;

    /**
     * @var PersonFieldManager
     */
    private $userFields;

    /**
     * @var OrganizationFieldManager
     */
    private $orgFields;

    /**
     * @param TicketFieldManager       $ticketFields
     * @param PersonFieldManager       $userFields
     * @param OrganizationFieldManager $orgFields
     */
    public function __construct(
        TicketFieldManager       $ticketFields,
        PersonFieldManager       $userFields,
        OrganizationFieldManager $orgFields
    ) {
        $this->ticketFields = $ticketFields;
        $this->userFields   = $userFields;
        $this->orgFields    = $orgFields;
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
                if (!$this->ticketFields->getFieldFromId($field->getFieldId())) {
                    return false;
                }
                break;

            case 'user_field':
                if (!$this->userFields->getFieldFromId($field->getFieldId())) {
                    return false;
                }
                break;

            case 'org_field':
                if (!$this->orgFields->getFieldFromId($field->getFieldId())) {
                    return false;
                }
                break;

            case 'category':
                return $this->ticketFields->isCategoryEnabled();
            case 'priority':
                return $this->ticketFields->isPriorityEnabled();
            case 'workflow':
                return $this->ticketFields->isWorkflowEnabled();
            case 'product':
                return $this->ticketFields->isProductEnabled();
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
