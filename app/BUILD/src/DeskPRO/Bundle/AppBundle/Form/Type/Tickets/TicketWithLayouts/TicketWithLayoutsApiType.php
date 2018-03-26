<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\Form\FormFields;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldRenderer\ApiFieldRenderer;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketWithLayouts\FieldResolver\ApiFieldResolver;
use DeskPRO\Bundle\AppBundle\Ticket\TicketLayoutFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketWithLayoutsApiType.
 */
class TicketWithLayoutsApiType extends AbstractType
{
    /**
     * @var TicketLayoutFactory
     */
    private $layoutFactory;

    /**
     * @var ApiFieldResolver
     */
    private $fieldResolver;

    /**
     * @var ApiFieldRenderer
     */
    private $fieldRenderer;

    /**
     * Constructor.
     *
     * @param ApiFieldResolver    $fieldResolver
     * @param ApiFieldRenderer    $fieldRenderer
     * @param TicketLayoutFactory $layoutFactory
     */
    public function __construct(ApiFieldResolver $fieldResolver, ApiFieldRenderer $fieldRenderer, TicketLayoutFactory $layoutFactory)
    {
        $this->fieldResolver = $fieldResolver;
        $this->fieldRenderer = $fieldRenderer;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onEnsureRequireFields'], 100);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TicketWithLayoutsManipulatorType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'field_resolver'  => $this->fieldResolver,
            'field_renderer'  => $this->fieldRenderer,
            'full_type_class' => TicketWithLayoutsApiFullType::class,
            'layout_factory'  => function ($department) {
                return $this->layoutFactory->getLayoutForTicketForm($department, true);
            },
        ]);
    }

    /**
     * We use partial updates for POST and PATCH request.
     * So we need to make sure that required fields are in the request for new tickets.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onEnsureRequireFields(FormEvent $event)
    {
        $ticket = $event->getForm()->getData();
        $data   = $event->getData();
        $form   = $event->getForm();

        // should applied for new tickets only
        if (!$ticket instanceof Ticket || $ticket->getId()) {
            return;
        }

        if ($form->has(FormFields::DEPARTMENT) && !isset($data[FormFields::DEPARTMENT])) {
            $data[FormFields::DEPARTMENT] = '';
        }
        if ($form->has(FormFields::SUBJECT) && !isset($data[FormFields::SUBJECT])) {
            $data[FormFields::SUBJECT] = '';
        }
        if ($form->has(FormFields::MESSAGE) && !isset($data[FormFields::MESSAGE])) {
            $data[FormFields::MESSAGE] = ['message' => ''];
        }

        $event->setData($data);
    }
}
