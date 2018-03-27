<?php

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
