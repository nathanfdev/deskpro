<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace Application\DeskPRO\WorkerProcess\Job;

use Application\DeskPRO\App;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Orb\Log\Logger;

/**
 * Class ChatTranscripts.
 */
class ChatTranscripts extends AbstractJob
{
    const DEFAULT_INTERVAL = 600;

    public function run()
    {
        $chatIds = App::getDb()->fetchAllCol("
            SELECT id
            FROM chat_conversations
            WHERE should_send_transcript = 1
              AND status = 'ended'
              AND ended_by != 'timeout'
              AND date_ended < ?
              AND date_ended > ?
        ", [date('Y-m-d H:i:s', time() - 300), date('Y-m-d H:i:s', time() - 18000)]);

        if (!$chatIds) {
            return;
        }

        App::getDb()->executeUpdate('
            UPDATE chat_conversations
            SET should_send_transcript = 0, date_transcript_sent = ?
            WHERE id IN (?)
        ', [date('Y-m-d H:i:s'), $chatIds], [\PDO::PARAM_STR, Connection::PARAM_INT_ARRAY]);

        foreach ($chatIds as $chatId) {
            $chat = App::getOrm()->find(ChatConversation::class, $chatId);

            $email = '';
            $name  = '';

            $person = $chat->getPerson();
            if ($person) {
                $email = $person->getPrimaryEmailAddress();
                $name  = $person->getName();
                App::getTranslator()->setPersonContext($chat->getPerson());
            }
            if (!$email && $chat->getPersonEmail()) {
                $email = $chat->getPersonEmail();
            }
            if (!$name && $chat->getPersonName()) {
                $name = $chat->getPersonName();
            }

            if ($email) {
                $chatMessages = App::getOrm()->createQuery('
                    SELECT m
                    FROM DeskPRO:ChatMessage m
                    WHERE m.conversation = ?1 AND m.is_user_hidden = false
                    ORDER BY m.id DESC
                ')->setParameter(1, $chat)->execute();

                $noAgentAnswer = true;

                /** @var ChatMessage $chatMessage */
                foreach ($chatMessages as $chatMessage) {
                    if (!$chatMessage->getIsSys() || ($chatMessage->getAuthor() && $chatMessage->getAuthor()->isAgent())) {
                        $noAgentAnswer = false;
                        break;
                    }
                }

                if (!$noAgentAnswer) {
                    $container = App::getContainer();
                    if ($container->get('deskpro.feature_flags')->hasFeature('new_email_templates')) {
                        $viewModel = $container->get('email.user_viewmodel_factory')
                            ->createChatTranscriptModel($chat, $chatMessages);
                        $container->get('email.email_sender')
                            ->send($viewModel, ['to' => $person]);
                    } else {
                        $vars = [
                            'convo'          => $chat,
                            'convo_messages' => $chatMessages,
                        ];

                        $message = App::getMailer()->createMessage();
                        $message->setTo($email, $name);
                        $message->setTemplate('DeskPRO:emails_user:chat-transcript.html.twig', $vars);
                        $message->setSuppressAutoreplies(true);
                        App::getMailer()->send($message);
                    }

                    // Add a chat log line for it
                    App::getDb()->insert('chat_messages', [
                        'conversation_id' => $chat->getId(),
                        'is_sys'          => 1,
                        'is_user_hidden'  => 0,
                        'is_html'         => 0,
                        'metadata'        => serialize(['phrase_id' => 'transcript_sent', 'email' => $email]),
                        'date_created'    => date('Y-m-d H:i:s'),
                    ]);
                }
            }

            App::getOrm()->detach($chat);
            $chat = null;
            App::getTranslator()->setPersonContext();
        }

        $this->logger->log('Sent '.count($chatIds).' chat transcripts', Logger::INFO);
    }
}
