<?php

namespace DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketAttachments;

use Application\DeskPRO\Entity\TicketAttachment;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\Form\Type\Attachments\AttachmentCollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BaseTicketMessageAttachmentCollectionType.
 */
class BaseTicketMessageAttachmentCollectionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, [$this, 'onPreSetData'], 100);
        $builder->addEventListener(FormEvents::POST_SUBMIT, [$this, 'onPostSubmit']);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'mapped'        => false,
                'entry_options' => function (Options $options) {
                    return [
                        'person' => $options['person'],
                        'label'  => false,
                    ];
                },
            ])
            ->setRequired(['ticket_message', 'entry_type'])
            ->setAllowedTypes('ticket_message', TicketMessage::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return AttachmentCollectionType::class;
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onPreSetData(FormEvent $event)
    {
        $message    = $this->getTicketMessage($event);
        $collection = new ArrayCollection();

        foreach ($message->getAttachments() as $attachment) {
            if ($this->isMatchingCriteria($event, $attachment)) {
                $collection->add($attachment);
            }
        }

        $event->setData($collection);
    }

    /**
     * @internal
     *
     * @param FormEvent $event
     *
     * @throws \Exception
     */
    public function onPostSubmit(FormEvent $event)
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
            if ($this->isMatchingCriteria($event, $attachment) && !$data->contains($attachment)) {
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

    /**
     * @param FormEvent        $event
     * @param TicketAttachment $attachment
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function isMatchingCriteria(FormEvent $event, TicketAttachment $attachment)
    {
        $entryType  = $event->getForm()->getConfig()->getOption('entry_type');
        $reflection = new \ReflectionClass($entryType);

        if (!$reflection->implementsInterface(TicketMessageAttachmentCollectionCriteriaInterface::class)) {
            throw new \Exception('Attachment form entry type should be instance of TicketMessageAttachmentCollectionCriteriaInterface');
        }

        return call_user_func([$entryType, 'matchedCriteria'], $attachment);
    }
}
