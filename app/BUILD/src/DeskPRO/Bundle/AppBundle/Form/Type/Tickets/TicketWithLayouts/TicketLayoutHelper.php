<?php

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

        // some of the form fields depends on person or brand form fields to re-set their data properly
        // so we should create person field first and detach/re-add in the proper display order

        $fieldResolver = $context->getFieldResolver();
        $fieldRenderer = $context->getFieldRenderer();

        $personField = new LayoutField(FormFields::PERSON);
        $fieldRenderer->addField($context, $personField, $fieldResolver->createFormField($context, $personField));

        $brandField = new LayoutField(FormFields::BRAND);
        if ($brandFormField = $fieldResolver->createFormField($context, $brandField)) {
            $fieldRenderer->addField($context, $brandField, $brandFormField);
        }

        $fields = self::getLayoutFields($context);
        foreach ($fields as $field) {
            if (!self::shouldBeAlwaysOnTheForm($field) && $field->hasCriteria() && !$matchedCriteria($field)) {
                continue;
            }

            if (in_array($field->getId(), [FormFields::PERSON, FormFields::BRAND])) {
                // as person field is already on the form we need to detach it and set in the proper display order
                $formField = $form->get($field->getId());
                $form->remove($field->getId());
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
            FormFields::BRAND,
            FormFields::PERSON,
            FormFields::SUBJECT,
            FormFields::MESSAGE,
            FormFields::ATTACHMENTS,
        ]);
    }
}
