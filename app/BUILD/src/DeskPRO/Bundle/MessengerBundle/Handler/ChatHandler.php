<?php

namespace DeskPRO\Bundle\MessengerBundle\Handler;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\EntityRepository\Department as DepartmentRepository;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\UserChat\UserChatEvent;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
use DeskPRO\Bundle\MessengerBundle\Mapper\ChatMapper;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatMessageEvent;
use DeskPRO\Component\Util\StringUtils;
use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ChatHandler
{
    const MESSAGE_TYPE_NEW_MESSAGE = 'chat.message';
    const CHAT_ENDED               = 'chat.ended';
    const CHAT_CREATE_TICKET       = 'chat.ticket.create';
    const CHAT_USER_TIMEOUT        = 'chat.userTimeout';
    const CHAT_TRANSCRIPT          = 'chat.transcript';
    const CHAT_RATING              = 'chat.rating';
    const CHAT_HISTORY             = 'chat.history';
    const CHAT_TRACK               = 'chat.track';
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
        self::CHAT_TRACK,
        self::CHAT_CREATE_TICKET,
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
     * @var BrandStack
     */
    private $brandStack;

    /**
     * ChatHandler constructor.
     *
     * @param ChatMapper               $mapper
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param BrandStack               $brandStack
     */
    public function __construct(
        ChatMapper $mapper,
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        BrandStack $brandStack
    ) {
        $this->chatMapper      = $mapper;
        $this->em              = $em;
        $this->eventDispatcher = $eventDispatcher;
        $this->brandStack      = $brandStack;
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
            throw new MessengerApiException(['type' => 'Wrong message type sent']);
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

        if (isset($request['blobs']) && !empty($request['blobs'])) {
            $blobIds = array_map('intval', $request['blobs']);
            $blobs   = $this->em->getRepository(Blob::class)->findBy(['id' => $blobIds]);
            $content = $message->getContent();
            foreach ($blobs as $blob) {
                if ($blob && StringUtils::ensureAttachment($blob, $content)) {
                    $this->em->persist($blob->setIsTemp(false));
                }
            }
        }

        $this->em->flush();

        $this->eventDispatcher->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent($chat->getChannelId('newmessage'), $message->getInfo())
        );

        $this->eventDispatcher->dispatch(
            ChatMessageEvent::EVENT_NAME,
            new ChatMessageEvent($chat->getId(), $message->getId(), ChatMessageEvent::CHAT_MESSAGE_EVENT_TYPE)
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
    private function handleChatRatingCommand(ChatConversation $chat, array $request)
    {
        $errors = [];
        if (!isset($request['rate'])) {
            $errors['rate'] = 'parameter wasn\'t sent';
        }
        if (!$chat->getDateEnded()) {
            $errors['chat'] = 'Cant\'t rate not ended chat';
        }

        if ($errors) {
            throw new MessengerApiException($errors);
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
    private function handleChatTranscriptCommand(ChatConversation $chat, array $request)
    {
        $chat->setShouldSendTranscript(true);

        $errors = [];

        if (!$chat->getPersonEmail() && (!isset($request['email']) || !trim($request['email']))) {
            $errors['email'] = 'You have to set email to receive transcript';
        } elseif (isset($request['email']) && trim($request['email'])) {
            $chat->setPersonEmail($request['email']);
        }

        if (isset($request['name']) && trim($request['name'])) {
            $chat->setPersonName($request['name']);
        }

        if ($errors) {
            throw new MessengerApiException($errors);
        }

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

    /**
     * @param ChatConversation $chat
     * @param array            $request
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    private function handleChatTrackCommand(ChatConversation $chat, array $request)
    {
        $trackMsg = $this->chatMapper->createUserTrackMessage($chat, $request);
        $chat->addMessage($trackMsg);
        $this->em->persist($trackMsg);
        $this->em->persist($chat);
        $this->em->flush();

        $this->eventDispatcher->dispatch(UserChatEvent::USER_TRACK, new UserChatEvent($chat, $trackMsg));
    }

    private function handleChatTicketCreateCommand(ChatConversation $chat, array $request)
    {
        $ticketRepository = $this->em->getRepository(Ticket::class);
        $ticket           = $ticketRepository->findOneBy(['linked_chat' => $chat]);
        if ($ticket) {
            return new ApiWrapper($ticket);
        }

        $ticket = new Ticket();
        $person = null;

        /** @var DepartmentRepository $departmentRepository */
        $departmentRepository = $this->em->getRepository(Department::class);
        /** @var PersonRepository $personRepository */
        $personRepository = $this->em->getRepository(Person::class);

        $errors = [];
        // try to find person
        if (isset($request['person_id'])) {
            $person = $this->em->find(Person::class, $request['person_id']);
        }
        if (!$person && isset($request['email'])) {
            $person = $personRepository->findOneByEmail($request['email']);
        }

        if (!$person && !isset($request['email'])) {
            $errors['email']     = 'Either email or person_id parameter is required';
            $errors['person_id'] = 'Either email or person_id parameter is required';
        }

        $department = null;
        if (isset($request['department_id'])) {
            $department = $departmentRepository->findOneBy(['id' => $request['department_id'], 'is_tickets_enabled' => 1]);
            if ($department) {
                $ticket->setDepartment($department);
            } else {
                $errors['department_id'] = 'Wrong id, department wasn\'t found';
            }
        } else {
            $errors['department_id'] = 'This parameter is required';
        }

        if ($errors) {
            throw new MessengerApiException($errors);
        }

        // determine username for person
        if (isset($request['name'])) {
            $username = $request['name'];
        } elseif ($person) {
            $username = $person->getDisplayName();
        } else {
            $username = 'anonymous user';
        }

        // if email was sent but person wasn't found - create person
        if (!$person) {
            $person = new Person();
            $person->setEmail($request['email']);
            $person->setName($username);
        }

        $ticket->setPerson($person);

        $ticketMessage = sprintf('Missed chat at %s.', $chat->getDateCreated()->format('Y-m-d H:i:s'));
        if ($msg = $this->chatMapper->createUserViewPageMessage($request)) {
            $ticketMessage .= '<br/>'.$msg;
        }
        $messages = $chat->getMessages()->filter(function ($message) {
            /* @var ChatMessage $message */
            return $message->getOrigin() == ChatMessage::ORIGIN_USER
                || $message->getOrigin() == ChatMessage::ORIGIN_AGENT;
        });

        foreach ($messages as $message) {
            /* @var ChatMessage $message */
            $ticketMessage .= '<br/>'.$message->getIsUser() ? 'agent: ' : 'user: '.$message->getContentHtml();
        }

        $ticket
            ->setSubject(sprintf('Missed chat with %s', $username))
            ->setDateCreated(new \DateTime())
            ->setCreationSystem(Ticket::CREATED_MESSENGER_UNANSWERED)
            ->setDepartment($department)
            ->setPerson($person)
            ->setStatus(Ticket::STATUS_AWAITING_AGENT)
            ->setBrand($this->brandStack->getActive()->getBrand())
            ->linked_chat = $chat;

        $message = new TicketMessage();
        $ticket->addMessage($message
            ->setPerson($person)
            ->setAsAgentNote(true)
            ->setMessageHtml($ticketMessage)
            ->setCreationSystem(TicketMessage::CREATED_MESSENGER_UNANSWERED)
            ->setDateCreated(new \DateTime())
        );
        $ticket->getTicketLogger()->recordExtra('suppress_user_notify', true);

        try {
            $this->em->beginTransaction();
            $this->em->persist($ticket);
            $this->em->persist($message);
            $this->em->persist($person);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw new MessengerApiException([], 'Failed to create a ticket', 400, $e);
        }

        return new ApiWrapper($ticket);
    }
}
