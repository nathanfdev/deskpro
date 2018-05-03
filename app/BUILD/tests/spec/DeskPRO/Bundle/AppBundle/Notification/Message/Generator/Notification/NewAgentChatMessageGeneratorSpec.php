<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Content\AvatarResolver;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification\NewAgentChatMessageGenerator;
use Doctrine\ORM\EntityManager;
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
        AgentChat $chat,
        AvatarResolver $avatarResolver
    ) {
        $this->beConstructedWith($em, $token_storage, $avatarResolver);
        $token_storage->getToken()->willReturn($token);
        $token->getUser()->willReturn($bob);

        $event->getMessageId()->willReturn(1);
        $event->getName()->willReturn(NewMessageEvent::EVENT_NAME);
        $em->getRepository(AgentChatMessage::class)->willReturn($repo);
        $repo->findOneBy(['id' => 1])->willReturn($message);

        $message->getChat()->willReturn($chat);
        $message->getPersonName()->willReturn('Bob');
        $message->getMessage()->willReturn('Hello, Alice');
        $message->getPerson()->willReturn($bob);
        $avatarResolver->getAvatar($bob, 64)->willReturn('http://lorempixel.com/64/64');

        $chat->getPersonList()->willReturn([$bob, $alice]);
        $chat->getId()->willReturn(1);

        $bob->getId()->willReturn(1);
        $alice->getId()->willReturn(2);
    }

    public function it_can_create_messages(NewMessageEvent $event)
    {
        $this->shouldHaveType('DeskPRO\Bundle\AppBundle\Notification\Message\Generator\Notification\NewAgentChatMessageGenerator');

        $this->createMessages($event);
    }

    public function it_can_check_if_it_can_create_message(
        NewMessageEvent $event,
        SystemEventInterface $another_event)
    {
        $this->canCreateMessage($event)->shouldBe(true);
        $this->canCreateMessage($another_event)->shouldBe(false);
    }
}
