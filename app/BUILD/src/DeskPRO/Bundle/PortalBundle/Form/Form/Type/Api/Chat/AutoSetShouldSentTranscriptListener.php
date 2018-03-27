<?php

namespace DeskPRO\Bundle\PortalBundle\Form\Form\Type\Api\Chat;

use Application\DeskPRO\Entity\ChatConversation;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Trait AutoSetShouldSentTranscriptListener.
 */
class AutoSetShouldSentTranscriptListener implements EventSubscriberInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::POST_SUBMIT => 'onSetShouldSentTranscript',
        ];
    }

    /**
     * If user has entered email then we can enable should send transcript option.
     *
     * @param FormEvent $event
     */
    public function onSetShouldSentTranscript(FormEvent $event)
    {
        /** @var ChatConversation $conversation */
        $conversation = $event->getData();
        if ($conversation->getPerson() || $conversation->getPersonEmail()) {
            $conversation->setShouldSendTranscript(true);
        }
    }
}
