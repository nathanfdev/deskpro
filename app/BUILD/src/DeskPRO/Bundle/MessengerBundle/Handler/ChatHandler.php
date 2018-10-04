<?php

namespace DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\MessengerBundle\Mapper\ChatMapper;
use Doctrine\ORM\EntityManager;

class ChatHandler
{
    const MESSAGE_TYPE_NEW_MESSAGE = 'chat.message';

    /**
     * @var array
     */
    private $availableMessages = [
        self::MESSAGE_TYPE_NEW_MESSAGE,
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
     * ChatHandler constructor.
     *
     * @param ChatMapper    $mapper
     * @param EntityManager $em
     */
    public function __construct(ChatMapper $mapper, EntityManager $em)
    {
        $this->chatMapper = $mapper;
        $this->em         = $em;
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
     * @throws \Exception
     */
    private function handleChatBlockRatingCommand(ChatConversation $chat, array $request)
    {
        if (!isset($request['rate'])) {
            throw new \Exception('"rate" parameter wasn\'t sent');
        }
        if (!$chat->getDateEnded()) {
            throw new \Exception('Cant\'t rate not ended chat');
        }
        if ($request['rate'] === true) {
            $chat->setRatingOverall(10);
        } else {
            $chat->setRatingOverall(10);
        }

        if (isset($request['comment'])) {
            $chat->setRatingComment($this->chatMapper->cleanText($request['comment']));
        }
    }
}
