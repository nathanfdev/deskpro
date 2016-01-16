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

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification\NewAgentChatMessageGenerator;
use Doctrine\ORM\EntityRepository;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @mixin NewAgentChatMessageGenerator.
 */
class NewAgentChatMessageGeneratorSpec extends ObjectBehavior
{
    public function let(
        EntityManager $em,
        TokenStorageInterface $token_storage,
        TokenInterface $token,
        Person $bob,
        Person $alice,
        EntityRepository $repo,
        NewMessageEvent $event,
        AgentChatMessage $message,
        AgentChat $chat
    ) {
        $this->beConstructedWith($em, $token_storage);
        $token_storage->getToken()->willReturn($token);
        $token->getUser()->willReturn($bob);

        $event->getMessageId()->willReturn(1);

        $em->getRepository('App:AgentChatMessage')->willReturn($repo);
        $repo->findOneBy(['id' => 1])->willReturn($message);

        $message->getChat()->willReturn($chat);
        $message->getPersonName()->willReturn('Bob');
        $message->getMessage()->willReturn('Hello, Alice');

        $chat->getPersonList()->willReturn([$bob, $alice]);

        $bob->getId()->willReturn(1);
        $alice->getId()->willReturn(2);
    }

    public function it_can_create_messages(NewMessageEvent $event)
    {
        //
        // TODO: fix test
        //

        //$this->createMessages($event);
    }

    public function it_can_check_if_it_can_create_message(
        NewMessageEvent $event,
        SystemEventInterface $another_event)
    {
        //
        // TODO: fix test
        //

        //$this->canCreateMessage($event)->shouldBe(true);
        //$this->canCreateMessage($another_event)->shouldBe(false);
    }
}
