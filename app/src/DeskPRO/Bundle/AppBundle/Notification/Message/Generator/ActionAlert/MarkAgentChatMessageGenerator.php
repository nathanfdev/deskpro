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

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;

/**
 * Class MarkAgentChatMessageGenerator.
 */
class MarkAgentChatMessageGenerator extends AbstractGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var MarkMessageEvent $event */
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            if ($target !== $this->getUser()->getId()) {
                $messages[] = new ActionAlert($target, $this->getData($event), $event->getName());
            }
        }

        return $messages;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return bool
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof MarkMessageEvent) {
            return true;
        }

        return false;
    }

    /**
     * @param MarkMessageEvent $event
     *
     * @return array
     */
    protected function getTargets(MarkMessageEvent $event)
    {
        $message = $this->getChatMessage($event);
        $targets = [];
        if ($message->getChat()->getType() !== 'everyone') {
            foreach ($message->getChat()->getPersonList() as $target) {
                $targets[] = $target->getId();
            }
        } else {
            foreach ($this->em->getRepository(Person::class)->findBy(['is_agent' => true]) as $agent) {
                $targets[] = $agent->getId();
            }
        }

        return $targets;
    }

    /**
     * @param MarkMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(MarkMessageEvent $event)
    {
        return [
            'message_id' => $event->getMessageId(),
            'message_uuid' => $this->getChatMessage($event)->getUuid(),
            'status'     => $event->getStatus(),
            'chat_id'    => $this->getChatMessage($event)->getChat()->getId(),
        ];
    }

    /**
     * @param MarkMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getChatMessage(MarkMessageEvent $event)
    {
        $messageRepo = $this->em->getRepository('App:AgentChatMessage');
        /** @var AgentChatMessage $message */
        $message = $messageRepo->findOneBy(['id' => $event->getMessageId()]);
        if (!$message) {
            throw new \InvalidArgumentException(sprintf('No message with id [ %s ] was found!', $event->getMessageId()));
        }

        return $message;
    }
}
