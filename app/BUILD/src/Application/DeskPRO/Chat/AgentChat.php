<?php

namespace Application\DeskPRO\Chat;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;

/**
 * Actions to do with AgentChat.
 * See the AgentChatController for more, there are some more that need to be decoupled.
 */
class AgentChat
{
    /** @var \Application\DeskPRO\Entity\Person */
    protected $person;
    /** @var bool */
    protected $suppressOfflineEmail = false;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->person = $person;
    }

    public function disableOfflineEmailAlert()
    {
        $this->suppressOfflineEmail = true;
    }

    public function sendMessage($message, $conversation)
    {
        if (!($conversation instanceof ChatConversation)) {
            $conversation = App::findEntity('DeskPRO:ChatConversation', $conversation);
        }

        $chatMessage = $conversation->addNewMessage(
            $message,
            $this->person
        );

        $channel = 'chat.message';
        if ($conversation['is_agent']) {
            $channel = 'agent_chat.new-message';
        }

        $partIds = [];
        foreach ($conversation->getParticipants() as $part) {
            $partIds[] = $part['id'];
        }

        App::getOrm()->transactional(function ($em) use ($conversation) {
            $em->persist($conversation);
            $em->flush();
        });

        $container       = App::getContainer();
        $eventDispatcher = $container->get('event_dispatcher');

        foreach ($conversation->getParticipants() as $part) {
            if ($part['id'] == $this->person['id']) {
                continue;
            }
            $date = clone $chatMessage['date_created'];
            $date->setTimeZone($part->getDateTimezone());
            $time = $container->getTranslator()->date('g:ia', $date, 'agent.time');

            $eventDispatcher->dispatch(
                LegacySystemEvent::EVENT_NAME,
                new LegacySystemEvent($channel, [
                    'conversation_id' => $conversation['id'],
                    'participant_ids' => $partIds,
                    'message_id'      => $chatMessage['id'],
                    'author_id'       => $chatMessage->author['id'],
                    'message'         => $chatMessage['content'],
                    'date_created'    => $chatMessage['date_created']->getTimestamp(),
                    'time'            => $time,
                    'target'          => $part->getId(),
                ]
            ));
        }

        // If any of the targets are not online, we might need to notify them of the message via email
        if (!$this->suppressOfflineEmail && !$chatMessage->is_sys) {
            foreach ($conversation->getParticipants() as $part) {
                if ($part['id'] == $this->person['id']) {
                    continue;
                }
                $session = App::getOrm()->getRepository(Session::class)->getSessionForPerson($part, 30);

                if (!$session && $part->getPref('agent_notif.chat_message.email')) {
                    if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
                        $viewModel = App::$container->get('email.agent_viewmodel_factory')
                            ->createAgentNewChatMessageModel($chatMessage);
                        App::$container->get('email.email_sender')
                            ->send($viewModel, ['to' => $this->person]);
                    } else {
                        $emailMessage = App::getMailer()->createMessage();
                        $emailMessage->setTemplate('DeskPRO:emails_agent:new-agent-chat-message.html.twig', [
                            'message' => $chatMessage,
                        ]);
                        $emailMessage->setToPerson($part);
                        App::getMailer()->send($emailMessage);
                    }
                }
            }
        }

        return [
            'conversation' => $conversation,
            'new_message'  => $chatMessage,
        ];
    }

    public function sendAgentMessage($message, array $agentIds, $convo_id = 0)
    {
        $em = App::getOrm();

        $agentIds = App::getContainer()->getAgentData()->confirmAgentIds($agentIds);

        $conversation = null;
        if ($convo_id) {
            $conversation = $em->find('DeskPRO:ChatConversation', $convo_id);
            if ($conversation and !$conversation->hasParticipant($this->person)) {
                // invalid convo if we're not part of it
                // sneaky hobitses
                $conversation = null;
            }
        }

        // Try to find an existing convo
        if (!$conversation) {
            $dateCut = new \DateTime('-5 hours');

            $findAgentIds   = $agentIds;
            $findAgentIds[] = $this->person['id'];

            $conversation = App::getEntityRepository(ChatConversation::class)->getRecentForPeople($findAgentIds, $dateCut);
        }

        if (!$conversation) {
            $conversation             = new ChatConversation();
            $conversation['is_agent'] = true;
            $conversation->addParticipant($this->person);
            foreach ($agentIds as $aid) {
                $conversation->addParticipant($aid);
            }
        }

        if (!$conversation || !count($conversation->getParticipants())) {
            return null;
        }

        $em->beginTransaction();
        $em->persist($conversation);
        $em->flush();
        $res = $this->sendMessage($message, $conversation);
        $em->commit();

        return $res;
    }
}
