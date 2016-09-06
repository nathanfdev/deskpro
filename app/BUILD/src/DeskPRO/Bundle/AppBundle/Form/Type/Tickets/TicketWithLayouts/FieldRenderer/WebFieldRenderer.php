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
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\TicketWithLayoutsContext;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Class WebFieldRenderer.
 */
class WebFieldRenderer implements FieldRendererInterface
{
    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param LanguageManager $languageManager
     */
    public function __construct(LanguageManager $languageManager)
    {
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function addField(TicketWithLayoutsContext $context, LayoutField $field, FormField $formField)
    {
        $form = $context->getForm();
        if ($form->has($field->getId())) {
            return;
        }

        $form->add($field->getId(), $formField->getType(), $formField->getOptions());

        // attachments field should have more_attachments button for portal
        if ($field->getFieldType() === FormFields::ATTACHMENTS) {
            $form->add('more_attachments', SubmitType::class, [
                'validation_groups' => false,
                'label'             => $this->languageManager->phrase('portal.forms.label_add_attachment'),
            ]);
        }
    }

    /**
     * @param TicketWithLayoutsContext $context
     */
    public function addSubmitButton(TicketWithLayoutsContext $context)
    {
        $form = $context->getForm();

        // if something is added, we need to ensure "submit" is removed (it's re-added at the end, below)
        if ($form->has('submit')) {
            $form->remove('submit');
        }

        if ($context->getVisibility() !== TicketWithLayoutsContext::VISIBILITY_NEW) {
            $label = $this->languageManager->phrase('portal.forms.label_save');
        } else {
            $label = $this->languageManager->phrase('portal.forms.label_submit');
        }

        $form->add('submit', SubmitType::class, [
            'label' => $label,
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     */
    public function addDisplayFields(TicketWithLayoutsContext $context)
    {
        $form = $context->getForm();
        if ($form->has('displayed_fields')) {
            $form->remove('displayed_fields');
        }

        $form->add('displayed_fields', HiddenType::class, [
            'mapped' => false,
        ]);
    }

    /**
     * @param TicketWithLayoutsContext $context
     */
    public function addRerenderField(TicketWithLayoutsContext $context)
    {
        $form = $context->getForm();
        if ($form->has('rerender_form')) {
            $form->remove('rerender_form');
        }

        $form->add('rerender_form', HiddenType::class, [
            'mapped' => false,
            'label'  => false,
        ]);
    }
}
