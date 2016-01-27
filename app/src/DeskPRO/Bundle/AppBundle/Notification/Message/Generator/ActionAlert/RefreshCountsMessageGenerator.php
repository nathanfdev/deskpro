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
use DeskPRO\Bundle\AppBundle\AgentChat\Helper;
use DeskPRO\Bundle\AppBundle\AgentChat\History;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\AbstractMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\MarkMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class RefreshCountsMessageGenerator.
 */
class RefreshCountsMessageGenerator extends AbstractGenerator
{
    /**
     * @var History
     */
    protected $searcher;

    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @param EntityManager         $em
     * @param TokenStorageInterface $token_storage
     * @param History               $searcher
     * @param Helper                $helper
     */
    public function __construct(EntityManager $em, TokenStorageInterface $token_storage, History $searcher, Helper $helper)
    {
        parent::__construct($em, $token_storage);
        $this->helper   = $helper;
        $this->searcher = $searcher;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var NewMessageEvent $event */
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new ActionAlert($target->getId(), $this->getData($target), 'refresh_counts');
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
        if ($event instanceof NewMessageEvent || $event instanceof MarkMessageEvent) {
            return true;
        }

        return false;
    }

    /**
     * @param AbstractMessageEvent $event
     *
     * @return Person[]
     */
    protected function getTargets(AbstractMessageEvent $event)
    {
        $message = $this->getChatMessage($event);
        $targets = [];
        if ($message->getChat()->getType() !== 'everyone') {
            foreach ($message->getChat()->getPersonList() as $target) {
                $targets[] = $target;
            }
        } else {
            foreach ($this->em->getRepository(Person::class)->findBy(['is_agent' => true]) as $agent) {
                $targets[] = $agent;
            }
        }

        return $targets;
    }

    /**
     * @param Person $person
     *
     * @return AgentChatMessage
     */
    protected function getData(Person $person)
    {
        return $this->helper->createCountResponse($this->searcher->countMessages($person));
    }

    /**
     * @param AbstractMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getChatMessage(AbstractMessageEvent $event)
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
