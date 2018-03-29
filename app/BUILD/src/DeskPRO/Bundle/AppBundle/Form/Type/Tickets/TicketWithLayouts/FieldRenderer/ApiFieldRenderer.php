<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer;

use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomDataType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomFields\CustomPerFieldType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;

/**
 * Class ApiFieldRenderer.
 */
class ApiFieldRenderer implements FieldRendererInterface
{
    private static $customDataMapping = [
        FormFields::TICKET_FIELD => 'fields',
        FormFields::ORG_FIELD    => 'organization_fields',
        FormFields::USER_FIELD   => 'user_fields',
        FormFields::CUSTOM_FIELD => 'contextual_fields',
    ];

    /**
     * {@inheritdoc}
     */
    public function addField(TicketWithLayoutsContext $context, LayoutField $field, FormField $formField)
    {
        $form = $context->getForm();

        if (in_array($formField->getType(), [CustomDataType::class, CustomPerFieldType::class])) {
            $customGroupName = self::$customDataMapping[$field->getFieldType()];
            if (!$form->has($customGroupName)) {
                $form->add($customGroupName, CombinedType::class, [
                    'forms'          => [],
                    'error_bubbling' => false,
                ]);
            }

            $customGroup = $form->get($customGroupName);
            if ($customGroup->has($field->getFieldId())) {
                return;
            }

            $form->remove($customGroupName);
            $customGroup->add($field->getFieldId(), $formField->getType(), $formField->getOptions());

            // re-add custom field group to map data properly
            $form->add($customGroup);
        } else {
            if ($form->has($field->getId())) {
                return;
            }

            $form->add($field->getId(), $formField->getType(), $formField->getOptions());
        }
    }
}
