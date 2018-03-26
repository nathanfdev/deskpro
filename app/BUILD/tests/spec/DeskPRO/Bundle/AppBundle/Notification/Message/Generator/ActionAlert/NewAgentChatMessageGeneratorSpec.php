<?php

namespace spec\DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Entity\AgentChat;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\AgentChat\NewMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert\NewAgentChatMessageGenerator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use JMS\Serializer\Serializer;
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
        Serializer $serializer,
        TokenInterface $token,
        Person $bob,
        Person $alice,
        EntityRepository $repo,
        NewMessageEvent $event,
        AgentChatMessage $message,
        AgentChat $chat
    ) {
        $this->beConstructedWith($em, $token_storage, $serializer);
        $token_storage->getToken()->willReturn($token);
        $token->getUser()->willReturn($bob);

        $event->getMessageId()->willReturn(1);
        $event->getName()->willReturn(NewMessageEvent::EVENT_NAME);

        $em->getRepository(AgentChatMessage::class)->willReturn($repo);
        $em->getRepository(Person::class)->willReturn($repo);
        $repo->findOneBy(['id' => 1])->willReturn($message);
        $repo->findBy(['is_agent' => true])->willReturn([$bob, $alice]);

        $message->getChat()->willReturn($chat);
        $message->getPersonName()->willReturn('Bob');
        $message->getMessage()->willReturn('Hello, Alice');

        $chat->getPersonList()->willReturn([$bob, $alice]);
        $chat->getType()->willReturn('team');

        $bob->getId()->willReturn(1);
        $alice->getId()->willReturn(2);
    }

    public function it_can_create_messages(NewMessageEvent $event)
    {
        $this->canCreateMessage($event)->shouldBe(true);
//        Comment untill we can solve inner serialization and how to handle it in spec
//        $messages = $this->createMessages($event);
//        $messages->shouldBeArray();
//        $messages->shouldHaveCount(2);
    }

    public function it_can_check_if_it_can_create_message(
        NewMessageEvent $event,
        SystemEventInterface $another_event)
    {
        $this->canCreateMessage($event)->shouldBe(true);
        $this->canCreateMessage($another_event)->shouldBe(false);
    }
}
