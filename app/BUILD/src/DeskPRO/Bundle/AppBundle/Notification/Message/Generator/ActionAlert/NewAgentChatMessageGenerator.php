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

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\AbstractGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadStore;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGenerator extends AbstractGenerator
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @param EntityManager         $em
     * @param TokenStorageInterface $token_storage
     * @param SerializerInterface   $serializer
     */
    public function __construct(EntityManager $em, TokenStorageInterface $token_storage, SerializerInterface $serializer)
    {
        parent::__construct($em, $token_storage);
        $this->serializer = $serializer;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var NewMessageEvent $event */
        $messages = [];
        foreach ($this->getTargets($event) as $target) {
            $messages[] = new ActionAlert($target, $this->getData($event), $event->getName());
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
        if ($event instanceof NewMessageEvent) {
            return true;
        }

        return false;
    }

    /**
     * @param NewMessageEvent $event
     *
     * @return array
     */
    protected function getTargets(NewMessageEvent $event)
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
     * @param NewMessageEvent $event
     *
     * @return AgentChatMessage
     */
    protected function getData(NewMessageEvent $event)
    {
        $message = $this->getChatMessage($event);

        $data = $this->extractData($message, $event);

        return $data;
    }

    private function extractData(AgentChatMessage $message, NewMessageEvent $event)
    {
        $context = new SideloadSerializationContext(new SideloadStore(), []);
        $data    = json_decode($this->serializer->serialize($message, 'json', $context), true);

        return $data;
    }

    /**
     * @param NewMessageEvent $event
     *
     * @return AgentChatMessage
     */
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
