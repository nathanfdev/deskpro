<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
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
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onLoadData'], 100);
        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onDeleteEmpty'], -1);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onSaveData']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'mapped'        => false,
                'entry_type'    => TicketMessageAttachmentType::class,
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
            ->setAllowedTypes('ticket_message', TicketMessage::class)
            ->setAllowedTypes('person', Person::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return CollectionType::class;
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

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onLoadData(FormEvent $event)
    {
        $message    = $this->getTicketMessage($event);
        $collection = new ArrayCollection();

        foreach ($message->getAttachments() as $attachment) {
            $collection->add($attachment);
        }

        $event->setData($collection);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     */
    public function onSaveData(FormEvent $event)
    {
        /** @var ArrayCollection $data */
        $data    = $event->getData();
        $message = $this->getTicketMessage($event);

        if (!$data instanceof ArrayCollection) {
            return;
        }

        foreach ($data as $attachment) {
            if ($attachment instanceof TicketAttachment) {
                $message->addAttachment($attachment);
            }
        }

        foreach ($message->getAttachments() as $attachment) {
            if (!$data->contains($attachment)) {
                $message->removeAttachment($attachment);
            }
        }
    }

    /**
     * @param FormEvent $event
     *
     * @return TicketMessage
     */
    private function getTicketMessage(FormEvent $event)
    {
        return $event->getForm()->getConfig()->getOption('ticket_message');
    }
}
