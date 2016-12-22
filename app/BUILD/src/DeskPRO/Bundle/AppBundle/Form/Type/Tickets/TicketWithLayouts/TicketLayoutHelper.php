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
use DeskPRO\Component\Util\ListUtils;

/**
 * Class TicketLayoutHelper.
 */
class TicketLayoutHelper
{
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
    public static function getExtractedData(array $submitted_data, TicketWithLayoutsContext $context)
    {
        $finalData = [];
        $keys      = [
            FormFields::DEPARTMENT,
            FormFields::PRODUCT,
            FormFields::CATEGORY,
            FormFields::WORKFLOW,
            FormFields::PRIORITY,
        ];

        foreach ($keys as $key) {
            if (array_key_exists($key, $submitted_data)) {
                $finalData[$key] = $submitted_data[$key];
            } else {
                $finalData[$key] = null;
            }
        }

        return $finalData;
    }

    /**
     * @param TicketWithLayoutsContext $context
     *
     * @return LayoutField[]
     */
    public static function getLayoutFields(TicketWithLayoutsContext $context)
    {
        $fields = [];
        foreach ($context->getActiveLayout()->all() as $field) {
            if (self::shouldBeAlwaysOnTheForm($field) || $context->hasValidVisibility($field)) {
                $fields[$field->getId()] = $field;
            }
        }

        return ListUtils::unique($fields);
    }

    /**
     * Render form fields.
     *
     * @param TicketWithLayoutsContext $context
     * @param callable                 $matchedCriteria
     */
    public static function renderFormFields(TicketWithLayoutsContext $context, callable $matchedCriteria)
    {
        // layout may changed or new fields could be added with new criteria checks depends on submitted data
        // so remove all the form fields and re-render them again
        // also it allows to render the layout fields in proper display order

        $form = $context->getForm();
        foreach ($form->all() as $child) {
            $form->remove($child->getName());
        }

        // some of the form fields depends on person form field to re-set their data properly
        // so we should create person field first and detach/re-add in the proper display order

        $fieldResolver = $context->getFieldResolver();
        $fieldRenderer = $context->getFieldRenderer();

        $personField = new LayoutField(FormFields::PERSON);
        $fieldRenderer->addField($context, $personField, $fieldResolver->createFormField($context, $personField));

        $fields = self::getLayoutFields($context);
        foreach ($fields as $field) {
            if (!self::shouldBeAlwaysOnTheForm($field) && $field->hasCriteria() && !$matchedCriteria($field)) {
                continue;
            }

            if ($field->getId() === FormFields::PERSON) {
                // as person field is already on the form we need to detach it and set in the proper display order
                $formField = $form->get(FormFields::PERSON);
                $form->remove(FormFields::PERSON);
                $form->add($formField);
            } else {
                $formField = $fieldResolver->createFormField($context, $field);
                if ($formField) {
                    $fieldRenderer->addField($context, $field, $formField);
                }
            }
        }
    }

    /**
     * We should ignore visibility and criteria validation for some fields to make sure they are always on the form.
     * We need this if layout configuration is not correct for some reason.
     *
     * @param LayoutField $field
     *
     * @return bool
     */
    public static function shouldBeAlwaysOnTheForm(LayoutField $field)
    {
        return in_array($field->getId(), [
            FormFields::DEPARTMENT,
            FormFields::PERSON,
            FormFields::SUBJECT,
            FormFields::MESSAGE,
            FormFields::ATTACHMENTS,
        ]);
    }
}
