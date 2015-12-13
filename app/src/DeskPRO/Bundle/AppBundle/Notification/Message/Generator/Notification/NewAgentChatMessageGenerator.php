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

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification;

use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Notification;

/**
 * Class NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGenerator extends AbstractGenerator
{
    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface
     */
    public function createMessage(SystemEventInterface $event)
    {
        $event->getName();
        /* @var NewMessageEvent $event */
        return new Notification($this->getTarget($event), $this->getData($event));
    }

    public function canCreateMessage(SystemEventInterface $event)
    {
        if ($event instanceof NewMessageEvent) {
            return true;
        }

        return false;
    }

    protected function getTarget(NewMessageEvent $event)
    {
        $message = $this->getChatMessage($event);
        foreach ($message->getChat()->getPersonList() as $target) {
            return $target->getId();
        }

        throw new \LogicException('No target was found!');
    }

    protected function getData(NewMessageEvent $event)
    {
        $message = $this->getChatMessage($event);

        return ['message' => $message->getMessage()];
    }

    protected function getChatMessage(NewMessageEvent $event)
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
