<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\TicketLayout;

use DeskPRO\Bundle\AppBundle\Form\FormFields;
use Application\DeskPRO\TicketLayout\Terms as LayoutFieldTerms;

class LayoutUtil
{
    /**
     * @param Layout $layout
     */
    public static function ensureMinimumUserLayout(Layout $layout)
    {
        if (!$layout->has(FormFields::MESSAGE)) {
            $layout->prepend(new LayoutField(FormFields::MESSAGE));
        }
        if (!$layout->has(FormFields::SUBJECT)) {
            $layout->prepend(new LayoutField(FormFields::SUBJECT));
        }
        if (!$layout->has(FormFields::PERSON)) {
            $layout->prepend(new LayoutField(FormFields::PERSON));
        }
        if (!$layout->has(FormFields::ATTACHMENTS)) {
            $layout->prepend(new LayoutField(FormFields::ATTACHMENTS));
        }
    }

    /**
     * Iterate all fields in Layout and returns all fields from their criteria which are not in Layout itself
     * I.e.:
     * . User Layout
     * . . ticket_field_1
     * . . . show only if user_field_1 = choice1
     *
     * => ticket_field_1 exists in layout but depends from user_field_1 which doesn't exist in layout directly
     * => return [LayoutField for user_field_1]
     *
     * @param Layout $layout
     * @return LayoutField[]
     */
    public static function getFieldsFromCriteriaNotInLayout(Layout $layout)
    {
        $fieldsNotInLayout = [];
        foreach ($layout->all() as $f) {
            foreach (self::getFieldsFromCritera($f) as $cf) {
                if (!$layout->has($cf->getId())) {
                    $fieldsNotInLayout[] = $cf;
                }
            }
        }

        return $fieldsNotInLayout;
    }

    /**
     * LayoutField might depends from other fields in criteria
     * Create fields by criteria
     *
     * @param LayoutField $field
     * @return LayoutField[]
     */
    private static function getFieldsFromCritera(LayoutField $field)
    {
        if (!$field->hasCriteria()) {
            return [];
        }

        $criteriaFields = [];
        foreach ($field->getCriteria()->getTerms() as $term) {
            $f = self::createLayoutFieldByTerm($term);
            if ($f) {
                $criteriaFields[] = $f;
            }
        }

        return $criteriaFields;
    }

    /**
     *
     * @param \Application\DeskPRO\TicketLayout\Terms\AbstractTicketLayoutTerm $term
     * @return boolean|\Application\DeskPRO\TicketLayout\LayoutField
     */
    private static function createLayoutFieldByTerm(LayoutFieldTerms\TicketLayoutTermInterface $term)
    {
        if ($term instanceof LayoutFieldTerms\CheckCategory) {
            return new LayoutField(FormFields::CATEGORY);
        } else if ($term instanceof LayoutFieldTerms\CheckDepartment) {
            return new LayoutField(FormFields::DEPARTMENT);
        } else if ($term instanceof LayoutFieldTerms\CheckPriority) {
            return new LayoutField(FormFields::PRIORITY);
        } else if ($term instanceof LayoutFieldTerms\CheckProduct) {
            return new LayoutField(FormFields::PRODUCT);
        } else if ($term instanceof LayoutFieldTerms\CheckWorkflow) {
            return new LayoutField(FormFields::WORKFLOW);
        } else if (
            $term instanceof LayoutFieldTerms\CheckUserField
            && $term->getTermOptions()
            && $term->getTermOptions()->has("field_id")
        ) {
            return new LayoutField(FormFields::USER_FIELD, $term->getTermOptions()->get("field_id"));
        } else if (
            $term instanceof LayoutFieldTerms\CheckOrgField
            && $term->getTermOptions()
            && $term->getTermOptions()->has("field_id")
        ) {
            return new LayoutField(FormFields::ORG_FIELD, $term->getTermOptions()->get("field_id"));
        } else if (
            $term instanceof LayoutFieldTerms\CheckTicketField
            && $term->getTermOptions()
            && $term->getTermOptions()->has("field_id")
        ) {
            return new LayoutField(FormFields::TICKET_FIELD, $term->getTermOptions()->get("field_id"));
        }

        return false;
    }

    /**
     * @param Layout $layout
     */
    public static function ensureMinimumAgentLayout(Layout $layout)
    {
        // nothing at the moment
    }
}
