<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Form\Type\ApiType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class TicketMessageType.
 */
class TicketMessageType extends ApiType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('message', 'html_textarea', [
                'mapped'   => false,
                'required' => true,
            ])
            ->add('format', 'choice', [
                'choices' => [
                    'html' => 'html',
                    'text' => 'text',
                ],
                'mapped' => false,
            ])
            ->add('is_note', 'api_boolean', [
                'property_path' => 'is_agent_note',
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'onSetMessage']);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSetRelations']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'ticket'        => null,
            'person'        => null,
            'error_mapping' => [
                'message' => 'message',
            ],
        ]);
    }

    /**
     * @param FormEvent $event
     */
    public function onSetMessage(FormEvent $event)
    {
        $data = $event->getData();
        if (empty($data['message'])) {
            return;
        }

        /** @var TicketMessage $message */
        $message = $event->getForm()->getData();
        if (isset($data['format']) && $data['format'] === 'text') {
            $message->setMessageText($data['message']);
        } else {
            $message->setMessageHtml($data['message']);
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onSetRelations(FormEvent $event)
    {
        /** @var TicketMessage $message */
        $message = $event->getData();

        /** @var Ticket $ticket */
        $ticket = $event->getForm()->getConfig()->getOption('ticket');
        if ($ticket) {
            $ticket->addMessage($message);
        }

        $person = $event->getForm()->getConfig()->getOption('person');
        if ($person) {
            $message->setPerson($person);
        }
    }
}
