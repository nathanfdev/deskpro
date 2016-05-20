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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer;

use Application\DeskPRO\TicketLayout\LayoutField;
use DeskPRO\Bundle\AppBundle\Form\FormField;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\CombinedType;
use DeskPRO\Bundle\AppBundle\Form\Type\CustomDataType;
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
    ];

    /**
     * {@inheritdoc}
     */
    public function addField(TicketWithLayoutsContext $context, LayoutField $field, FormField $formField)
    {
        $form = $context->getForm();

        if ($formField->getType() === CustomDataType::class) {
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

    /**
     * {@inheritdoc}
     */
    public function removeField(TicketWithLayoutsContext $context, LayoutField $field)
    {
        $form = $context->getForm();

        if (isset(self::$customDataMapping[$field->getFieldType()])) {
            // remove custom fields from the layout in api context
            $groupName = self::$customDataMapping[$field->getFieldType()];
            if ($form->has($groupName)) {
                $customGroup = $form->get($groupName);
                if ($customGroup->has($field->getFieldId())) {
                    $customGroup->remove($field->getFieldId());
                }
            }
        } else {
            if ($form->has($field->getId())) {
                $form->remove($field->getId());
            }
        }
    }
}
