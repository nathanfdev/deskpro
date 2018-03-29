<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketParticipants;

use Application\DeskPRO\Entity\TicketParticipant;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonAssignType;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketDisableAutoProcessListener;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketParticipantType.
 */
class TicketParticipantType extends AbstractTicketParticipantType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $personConstraints = [];
        if (!$options['allow_all']) {
            $personConstraints[] = new AppAssert\Person\PersonType([
                'type' => 'user',
            ]);
        }

        $builder->add('person', PersonAssignType::class, [
            'error_bubbling' => $options['inline'],
            'constraints'    => $personConstraints,
            'allow_create'   => true,
        ]);

        $builder->addEventSubscriber(new TicketDisableAutoProcessListener());
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations'], 100);

        if ($options['inline']) {
            $builder->addEventListener(FormEvents::POST_SET_DATA, [$this, 'onSetInlineEmailField']);
            $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetInlineData']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'inline'         => false,
                'set_owner'      => true,
                'data_class'     => TicketParticipant::class,
                'error_bubbling' => false,
                'error_mapping'  => function (Options $options) {
                    return $options['inline'] ? [] : ['.' => 'person'];
                },
            ])
            ->setAllowedTypes('inline', 'boolean')
            ->setAllowedTypes('set_owner', 'boolean')
        ;
    }

    /**
     * Add person email field to keep and validate email address.
     * Uses to transform data to single input and display errors properly.
     *
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInlineEmailField(FormEvent $event)
    {
        $data  = $event->getData();
        $email = null;

        if ($data instanceof TicketParticipant) {
            $person = $data->getPerson();
            if ($person && $person->getPrimaryEmail()) {
                $email = $person->getPrimaryEmail()->getEmail();
            } elseif ($data->getPersonEmail()) {
                $email = $data->getPersonEmail()->getEmail();
            }
        }

        $event->getForm()->add('person_email', TextType::class, [
            'data'           => $email,
            'mapped'         => false,
            'error_bubbling' => true,
        ]);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetInlineData(FormEvent $event)
    {
        $data = $event->getData();

        if (is_scalar($data)) {
            $event->setData([
                'person'       => $data,
                'person_email' => $data,
            ]);
        }
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        $form    = $event->getForm();
        $data    = $event->getData();
        $options = $form->getConfig()->getOptions();

        if ($data instanceof TicketParticipant) {
            if (!$data->getId() && $options['set_owner']) {
                $options['owner']->addParticipant($data);
            }
            if ($data->getPerson()) {
                $data->setPersonEmail($data->getPerson()->getPrimaryEmail());
            }
        }
    }
}
