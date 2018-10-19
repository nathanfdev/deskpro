<?php

namespace DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use DeskPRO\Bundle\AppBundle\Notification\Event\Messenger\ChatEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\MessengerBundle\Mapper\ChatMapper;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChatHandler
{
    const MESSAGE_TYPE_NEW_MESSAGE = 'chat.message';
    const CHAT_ENDED               = 'chat.ended';
    const CHAT_USER_TIMEOUT        = 'chat.userTimeout';
    const CHAT_TRANSCRIPT          = 'chat.block.transcript';
    const CHAT_RATING              = 'chat.block.rating';
    const TYPING_START             = 'typing.start';
    const TYPING_END               = 'typing.end';

    /**
     * @var array
     */
    private $availableMessages = [
        self::MESSAGE_TYPE_NEW_MESSAGE,
        self::CHAT_ENDED,
        self::CHAT_USER_TIMEOUT,
        self::CHAT_TRANSCRIPT,
        self::CHAT_RATING,
        self::TYPING_START,
        self::TYPING_END,
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
        if (!in_array($request['type'], $this->availableMessages)) {
            throw new \Exception('Wrong command given');
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

        return array_map(
            function ($message) use ($chatMapper) {
                return $this->chatMapper->mapMessageToArray($message);
            },
            array_filter($chat->getMessages(), function ($message) {
                /* @var ChatMessage $message */
                return !$message->getIsUserHidden();
            })
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
            self::CHAT_TRANSCRIPT,
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

        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), self::CHAT_TRANSCRIPT));

        return new ApiWrapper($chat);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @return ApiWrapper
     */
    private function handleTypingStartCommand(ChatConversation $chat, array $request)
    {
        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), self::TYPING_START));

        return new ApiWrapper($chat);
    }

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @return ApiWrapper
     */
    private function handleTypingEndCommand(ChatConversation $chat, array $request)
    {
        $this->eventDispatcher->dispatch(ChatEvent::EVENT_NAME, new ChatEvent($chat->getId(), self::TYPING_END));

        return new ApiWrapper($chat);
    }
}
