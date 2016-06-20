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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TicketMessageAttachmentCollectionType.
 */
class TicketMessageAttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], 100);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onRemoveTicketAttachments'], 50);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onDeleteEmpty'], -1);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'entry_type'    => 'ticket_message_attachment',
                'entry_options' => function (Options $options) {
                    return [
                        'ticket_message' => $options['ticket_message'],
                        'person'         => $options['person'],
                        'label'          => false,
                    ];
                },
                'allow_add'      => true,
                'allow_delete'   => true,
                'label'          => false,
                'error_bubbling' => false,
            ])
            ->setRequired([
                'ticket_message',
                'person',
            ])
            ->setAllowedTypes([
                'ticket_message' => TicketMessage::class,
                'person'         => Person::class,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ticket_message_attachment_collection';
    }

    /**
     * Ensure there is a collection of attachments on the message (even if empty).
     *
     * @param FormEvent $event
     */
    public function onPreSetData(FormEvent $event)
    {
        $data = $event->getData();
        if (!$data instanceof Collection) {
            $event->setData(new ArrayCollection());
        }
    }

    /**
     * Remove message attachments from the ticket entity.
     *
     * We have a collection of all ticket attachments as ticket property (including current message attachments).
     * So we need to reset it to re-fill it via ticket message again or we can't delete attachments (they would be still assigned to the ticket).
     *
     * @param FormEvent $event
     */
    public function onRemoveTicketAttachments(FormEvent $event)
    {
        /** @var ArrayCollection $data */
        $data = $event->getData();
        $form = $event->getForm();

        /** @var TicketMessage $ticket_message */
        $ticket_message = $form->getConfig()->getOption('ticket_message');
        if (!$ticket_message) {
            return;
        }

        $ticket = $ticket_message->getTicket();
        if ($ticket) {
            /** @var ArrayCollection $ticket_attachments */
            $ticket_attachments = $ticket->attachments;

            foreach ($data as $attachment) {
                if ($ticket_attachments->contains($attachment)) {
                    $ticket_attachments->removeElement($attachment);
                }
            }
        }
    }

    /**
     * @param FormEvent $event
     */
    public function onDeleteEmpty(FormEvent $event)
    {
        /** @var ArrayCollection $data */
        $data = $event->getData();
        foreach ($data as $key => $attachment) {
            if (!$attachment) {
                $data->remove($key);
            }
        }

        $event->setData(new ArrayCollection($data->getValues()));
    }
}
