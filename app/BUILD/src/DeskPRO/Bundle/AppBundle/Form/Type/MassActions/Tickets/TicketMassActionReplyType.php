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

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onPreSubmit']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
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
    public function onPreSubmit(FormEvent $event)
    {
        $data = $event->getData();

        // ensure required fields
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
    public function onPostSubmit(FormEvent $event)
    {
        $data    = $event->getData();
        $options = $event->getForm()->getConfig()->getOptions();

        // set person
        if ($data instanceof TicketMessage) {
            $data->setPerson($options['person']);
        }
    }
}
