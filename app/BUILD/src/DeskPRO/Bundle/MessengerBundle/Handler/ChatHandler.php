<?php

namespace DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\MessengerBundle\Exception\MapperException;
use DeskPRO\Bundle\MessengerBundle\Mapper\ChatMapper;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChatHandler
{
    const MESSAGE_TYPE_NEW_MESSAGE = 'chat.message';
    const CHAT_ENDED               = 'chat.ended';
    const CHAT_USER_TIMEOUT        = 'chat.userTimeout';
    const CHAT_TRANSCRIPT          = 'chat.block.transcript';
    const CHAT_RATING              = 'chat.block.rating';
    const CHAT_HISTORY             = 'chat.history';
    const TYPING_START             = 'chat.typing.start';
    const TYPING_END               = 'chat.typing.end';

    /**
     * @var array
     */
    private $availableCommands = [
        self::MESSAGE_TYPE_NEW_MESSAGE,
        self::CHAT_ENDED,
        self::CHAT_USER_TIMEOUT,
        self::CHAT_TRANSCRIPT,
        self::CHAT_RATING,
        self::TYPING_START,
        self::TYPING_END,
        self::CHAT_HISTORY,
    ];

    /**
     * @var ChatMapper
     */
    private $chatMapper;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * ChatHandler constructor.
     *
     * @param ChatMapper               $mapper
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(ChatMapper $mapper, EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->chatMapper      = $mapper;
        $this->em              = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Exception
     *
     * @return array
     */
    public function handle(ChatConversation $chat, array $request)
    {
        $commandType = $request['type'];
        if (!in_array($commandType, $this->availableCommands)) {
            throw new MapperException(['type' => 'Wrong message type sent']);
        }

        $commandName = 'handle'.implode(
            '',
            array_map(
                function ($element) {
                    return ucfirst($element);
                },
                explode('.', $commandType)
            )
        ).'Command';

        return $this->$commandName($chat, $request);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return array
     */
    private function handleChatMessageCommand(ChatConversation $chat, array $request)
    {
        $message = $this->chatMapper->createChatMessage($request);
        $chat->addMessage($message);
        $this->em->persist($message);
        $this->em->persist($chat);
        $this->em->flush();

        $this->eventDispatcher->dispatch(
            ChatMessageEvent::EVENT_NAME,
            new ChatMessageEvent(
                $chat->getId(),
                $message->getId(),
                ChatMessageEvent::CHAT_MESSAGE_EVENT_TYPE
            )
        );

        return $this->chatMapper->mapMessageToArray($message);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return array
     */
    private function handleChatHistoryCommand(ChatConversation $chat, array $request)
    {
        $chatMapper = $this->chatMapper;

        return array_values(array_map(
            function ($message) use ($chatMapper) {
                return $this->chatMapper->mapMessageToArray($message);
            },
            array_filter($chat->getMessages()->toArray(), function ($message) {
                /* @var ChatMessage $message */
                return !$message->getIsUserHidden();
            }))
        );
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Exception
     *
     * @return ApiWrapper
     */
    private function handleChatBlockRatingCommand(ChatConversation $chat, array $request)
    {
        if (!isset($request['rate'])) {
            throw new \Exception('"rate" parameter wasn\'t sent');
        }
        if (!$chat->getDateEnded()) {
            throw new \Exception('Cant\'t rate not ended chat');
        }

        $eventData = [];

        if ($request['rate'] === true) {
            $chat->setRatingOverall(10);
        } else {
            $chat->setRatingOverall(1);
        }

        $eventData['rate'] = $request['rate'];

        if (isset($request['comment'])) {
            $chat->setRatingComment($this->chatMapper->cleanText($request['comment']));
            $eventData['comment'] = $chat->getRatingComment();
        }

        $this->em->persist($chat);
        $this->em->flush();

        $event = new ChatEvent(
            $chat->getId(),
            ChatEvent::CHAT_RATING_EVENT_TYPE,
            $eventData
        );

        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, $event);

        return new ApiWrapper($chat);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return ApiWrapper
     */
    private function handleChatBlockTranscriptCommand(ChatConversation $chat, array $request)
    {
        $chat->setShouldSendTranscript(true);

        $this->em->persist($chat);
        $this->em->flush();

        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), ChatEvent::CHAT_TRANSCRIPT_EVENT_TYPE));

        return new ApiWrapper($chat);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @return ApiWrapper
     */
    private function handleChatTypingStartCommand(ChatConversation $chat, array $request)
    {
        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), ChatEvent::TYPING_START_EVENT_TYPE));

        return new ApiWrapper($chat);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @return ApiWrapper
     */
    private function handleChatTypingEndCommand(ChatConversation $chat, array $request)
    {
        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), ChatEvent::TYPING_END_EVENT_TYPE));

        return new ApiWrapper($chat);
    }
}
