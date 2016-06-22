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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Hierarchy\HierarchyNode;
use DeskPRO\Component\Util\ListUtils;
use Symfony\Component\Form\AbstractType;

/**
 * Class TicketLayoutHelper.
 */
class TicketLayoutHelper extends AbstractType
{
    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldHasCriteriaAndCriteriaDoesNOTMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && !$field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldHasCriteriaAndItDOESMatch(LayoutField $field, $extracted_data)
    {
        return $field->getCriteria() && $field->getCriteria()->isSubmittedDataMatch($extracted_data);
    }

    /**
     * @param LayoutField $field
     * @param array       $extracted_data
     *
     * @return bool
     */
    public function fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches(LayoutField $field, $extracted_data)
    {
        return !$field->getCriteria() || ($field->getCriteria() && $field->getCriteria()->isSubmittedDataMatch($extracted_data));
    }

    /**
     * Submitted choice values are not submitted with the entity Id. Instead we are given the choice list key.
     *
     * This inspects the submitted data on our form and gives us data we're interested in.
     *
     * @param array                    $submitted_data
     * @param TicketWithLayoutsContext $context
     *
     * @return array the form key and its selected entity ID (or null if not submitted)
     */
    public function getExtractedData(array $submitted_data, TicketWithLayoutsContext $context)
    {
        $form       = $context->getForm();
        $final_data = [];
        $keys       = [
            FormFields::DEPARTMENT,
            FormFields::PRODUCT,
            FormFields::CATEGORY,
            FormFields::WORKFLOW,
            FormFields::PRIORITY,
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $submitted_data) && $form->has($key)) {
                $submitted_value = $submitted_data[$key];

                $choice = null;
                if (is_scalar($submitted_value)) {
                    $choiceList = $form->get($key)->getConfig()->getOption('choice_list');
                    if ($choiceList) {
                        $choice = current($choiceList->getChoicesForValues([$submitted_value]));
                    }
                }

                if ($choice instanceof HierarchyNode) {
                    $choice = $choice->getData();
                }

                if ($choice) {
                    $final_data[$key] = $choice->getId();
                } else {
                    $final_data[$key] = null;
                }
            } else {
                $final_data[$key] = null;
            }
        }

        return $final_data;
    }

    /**
     * @param TicketWithLayoutsContext $context
     * @param array                    $extractedData
     *
     * @return TicketLayoutChanges
     */
    public function getLayoutChanges(TicketWithLayoutsContext $context, $extractedData = [])
    {
        $prevLayout = $context->getPreviouslyActiveLayout();
        $newLayout  = $context->getActiveLayout();

        $additionalFields = [];
        $fieldsToRemove   = ListUtils::filter($prevLayout->all(), function (LayoutField $f) use ($newLayout) {
            return !$newLayout->has($f->getId());
        });

        // We need to figure out which fields have been added or removed from the form
        // This can be simple (e.g. dependant on the department layout) or more complicated,
        // like being dependant on criteria

        $fieldsRequiringRerender = [];
        foreach ($newLayout->all() as $field) {
            if ($context->fieldWasDisplayedBefore($field)) {
                // this field was displayed before. should it continue to be displayed?
                if (!$context->hasValidVisibility($field) || $this->fieldHasCriteriaAndCriteriaDoesNOTMatch($field, $extractedData)) {
                    $fieldsToRemove[] = $field;
                }
            } else {
                // this field was not displayed before, but should it be added and the form re-rendered?
                if ($context->hasValidVisibility($field) && $this->fieldDoesNotHaveCriteriaOrHasCriteriaAndMatches($field, $extractedData)) {
                    $additionalFields[] = $field;

                    if (!$context->fieldWasDisplayedBefore($field)) {
                        $fieldsRequiringRerender[] = $field;
                    }
                }
            }
        }

        $additionalFields = ListUtils::unique($additionalFields);

        return new TicketLayoutChanges($fieldsRequiringRerender, $fieldsToRemove, $additionalFields);
    }
}
