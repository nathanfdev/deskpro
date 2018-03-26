<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\MassActions\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiBooleanType;
use DeskPRO\Bundle\AppBundle\Form\Type\HtmlTextareaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketMassActionReplyType.
 */
class TicketMassActionReplyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', HtmlTextareaType::class)
            ->add('is_agent_note', ApiBooleanType::class)
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onEnsureRequiredFields']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetPerson']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class'     => TicketMessage::class,
                'error_bubbling' => false,
            ])
            ->setRequired('person')
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onEnsureRequiredFields(FormEvent $event)
    {
        $data = $event->getData();
        if (is_array($data) && !array_key_exists('message', $data)) {
            $data['message'] = '';
            $event->setData($data);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetPerson(FormEvent $event)
    {
        $data    = $event->getData();
        $options = $event->getForm()->getConfig()->getOptions();

        if ($data instanceof TicketMessage) {
            $data->setPerson($options['person']);
        }
    }
}
