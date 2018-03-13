<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Chat\UserChat;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\ChatFieldManager;
use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use Application\DeskPRO\People\ActivityLogger\ActivityLogger;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use Doctrine\ORM\EntityManager;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Manages interactions between users, agents and the server.
 */
class UserChatManager
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $db;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var \Application\DeskPRO\Translate\Translate
     */
    protected $tr;

    /**
     * @var \Application\DeskPRO\Entity\Session
     */
    protected $session;

    /**
     * @var Person
     */
    protected $person;

    /**
     * @var \Application\DeskPRO\Chat\UserChat\AutoAssigner
     */
    protected $auto_assigner;

    /**
     * @var \Application\DeskPRO\People\ActivityLogger\ActivityLogger
     */
    protected $activityLogger;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct(
        Session $session = null,
        EntityManager $em,
        Translate $translate,
        ActivityLogger $logger,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->em              = $em;
        $this->db              = $em->getConnection();
        $this->tr              = $translate;
        $this->activityLogger  = $logger;
        $this->eventDispatcher = $eventDispatcher;

        if ($session) {
            $this->session = $session;
            $this->person  = $session->getPerson();
        }
    }

    /**
     * Set the auto-assigner.
     *
     * @param $assigner
     */
    public function setAutoAssigner(AutoAssigner $assigner)
    {
        $this->auto_assigner = $assigner;
    }

    /**
     * Start a new chat conversation, or if its within time and still open, resume the previous.
     *
     * @param array $chat_options
     * @param bool  $is_window_mode
     * @param bool  $error_code
     *
     * @throws \Exception
     *
     * @return ChatConversation|null
     */
    public function startChat(array $chat_options, $is_window_mode = false, &$error_code = false)
    {
        $convo = $this->em->getRepository(ChatConversation::class)->getLatestChatForSession($this->session);

        $is_new_convo = false;
        $new_person   = false;
        /** @var ChatFieldManager $field_manager */
        $field_manager = App::getSystemService('chat_fields_manager');
        $chat_fields   = @$chat_options['chat_fields'] ?: [];

        if (!$convo) {
            $convo          = new ChatConversation();
            $convo->session = $this->session;
            if ($this->person) {
                $convo->person = $this->person;
            }

            if (!empty($chat_options['department_id'])) {
                $dep = $this->em->getRepository('DeskPRO:Department')->find($chat_options['department_id']);
                if ($dep) {
                    $convo->department = $dep;
                }
            }

            if (isset($chat_options['chat_fields'])) {
                if ($errors = $this->validateCustomFields($chat_fields)) {
                    $error_code = 'invalid_custom_fields';

                    return;
                }
            }

            // Spam trap
            $traps = [@$chat_options['full_name'], @$chat_options['email_address']];
            $traps = Arrays::func($traps, 'trim');
            $traps = Arrays::removeEmptyString($traps);
            if (count($traps) || @$chat_options['email_address2'] != 'yes') {
                $error_code = 'person_disabled';

                return;
            }

            $chat_options['name']  = empty($chat_options['name']) ? '' : $chat_options['name'];
            $chat_options['email'] = empty($chat_options['email']) ? '' : $chat_options['email'];

            // Mixed up name/email boxes
            if ($chat_options['name'] && $chat_options['email'] && StringEmail::isValueValid($chat_options['name']) && !StringEmail::isValueValid($chat_options['email'])) {
                $tmp                   = $chat_options['email'];
                $chat_options['email'] = $chat_options['name'];
                $chat_options['name']  = $tmp;
                // Put email into name box
            } elseif ($chat_options['name'] && !$chat_options['email'] && StringEmail::isValueValid($chat_options['name'])) {
                $chat_options['email'] = $chat_options['name'];
                $chat_options['name']  = '';
            }

            if (!empty($chat_options['name'])) {
                $convo->person_name = $chat_options['name'];
            }
            if (!empty($chat_options['email']) && \Orb\Validator\StringEmail::isValueValid($chat_options['email']) && !App::$container->getEmailAccountManager()->findAccountForEmailAddress($chat_options['email'])) {
                $convo->person_email = $chat_options['email'];

                $related_person = $this->em->getRepository('DeskPRO:Person')->findOneByEmail($chat_options['email']);
                if ($related_person) {
                    $convo->person = $related_person;
                } else {
                    $new_person = Person::newContactPerson();
                    if ($convo->person_name) {
                        $new_person->name = $convo->person_name;
                    }
                    $new_person->setEmail($convo->person_email);
                    $convo->person        = $new_person;
                    $new_person->language = $this->tr->getLanguage();
                }
            }
            $is_new_convo = true;

            if ($convo->person && $convo->person->is_disabled) {
                $error_code = 'person_disabled';

                return;
            }
        }

        if ($is_window_mode) {
            $convo['is_window'] = true;
        }

        $this->em->beginTransaction();

        try {
            if ($new_person) {
                $this->em->persist($new_person);
                $this->em->flush();
            }

            $this->em->persist($convo);
            $this->em->flush();

            if (isset($chat_options['chat_fields'])) {
                $field_manager->saveFormToObject($chat_fields, $convo);
            }

            if ($is_new_convo) {
                $this->addSystemMessage($convo, 'message_started', [], [
                    'user_hidden' => true,
                    'is_html'     => false,
                ]);
                if (isset($_GET['parent_url']) && is_string($_GET['parent_url'])) {
                    $this->addUserTrack($convo, $_GET['parent_url']);
                } else {
                    $url = '';
                    if ($k = strpos($url, 'parent_url=')) {
                        $str  = substr($url, $k);
                        $vars = null;
                        @parse_str($str, $vars);

                        if (!empty($vars['parent_url']) && is_string($vars['parent_url'])) {
                            $url = $vars['parent_url'];
                        }
                    }

                    $this->addUserTrack($convo, $url);
                }
            }

            if (!$convo->agent && $this->auto_assigner) {
                $assign_agent = $this->auto_assigner->getAgent($convo);
                if ($assign_agent) {
                    $this->assignAgent($convo, $assign_agent);
                }
            }

            $this->em->flush();

            if (isset($chat_options['content']) && $chat_options['content']) {
                $this->addUserMessage($convo, $chat_options['content']);
                $newchat_cm_data['initial_message'] = $chat_options['content'];

                $this->em->flush();
            }

            if ($is_new_convo) {
                $newchat_cm_data = $convo->getInfo();

                $this->eventDispatcher->dispatch(
                    UserChatEvent::EVENT_NAME,
                    new UserChatEvent('chat.new', $newchat_cm_data)
                );
            }

            if ($convo->person) {
                $action = new \Application\DeskPRO\People\ActivityLogger\ActionType\NewChat($convo->person, $convo);
                $this->activityLogger->saveAction($action);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        return $convo;
    }

    public function validateCustomFields($data)
    {
        $field_manager         = App::getSystemService('chat_fields_manager');
        $trans                 = App::getTranslator();
        $invalid_custom_fields = [];

        foreach ($field_manager->getFields() as $field) {
            $errors = $field->getHandler()->validateFormData($data);
            foreach ($errors as $code) {
                $code = preg_replace('#.*?\.(.*?)$#', '$1', $code);
                switch ($code) {
                    case 'min_length':
                        $code = 'text_min';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    case 'max_length':
                        $code = 'text_max';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    case 'regex_fail':
                        $code = 'text_regex';
                        $msg  = $trans->getPhraseText('user.error.form_'.$code);
                        break;
                    default:
                        $msg = $trans->getPhraseText('user.error.form_'.$code);
                }
                $invalid_custom_fields['field_'.$field->getId()] = $msg;
            }
        }

        return $invalid_custom_fields;
    }

    /**
     * Get an open chat for the users session.
     *
     * @return ChatConversation
     */
    public function getChat($allow_timeout = false)
    {
        $convo = $this->em->getRepository('DeskPRO:ChatConversation')->getLatestChatForSession($this->session, $allow_timeout);

        return $convo;
    }

    /**
     * @param $chat
     */
    public function reopenTimoutChat(ChatConversation $convo)
    {
        $convo['status']     = 'open';
        $convo['date_ended'] = null;
        $convo['ended_by']   = '';

        $this->em->beginTransaction();
        try {
            $this->em->persist($convo);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $this->addSystemMessage(
            $convo,
            'message_user-returned'
        );

        // Resend the new chat alerts to agents
        $newchat_cm_data              = $convo->getInfo();
        $newchat_cm_data['restarted'] = true;

        $this->eventDispatcher->dispatch(
            UserChatEvent::EVENT_NAME,
            new UserChatEvent('chat.new', $newchat_cm_data)
        );
    }

    /**
     * @param ChatConversation $convo
     * @param Person           $person
     *
     * @throws \Exception
     */
    public function personJoined(ChatConversation $convo, Person $person)
    {
        $tag1 = 'user_joined.'.$person->getId();
        $tag2 = 'user_left.'.$person->getId();

        $joined_left_counts = App::getDb()->fetchAllKeyValue('
            SELECT tag, COUNT(*)
            FROM chat_messages
            WHERE conversation_id = ? AND tag IN (?, ?)
            GROUP BY tag
        ', [$convo->getId(), $tag1, $tag2]);

        if (
            $joined_left_counts
            && isset($joined_left_counts[$tag1])
            && isset($joined_left_counts[$tag2])
            && $joined_left_counts[$tag1] != $joined_left_counts[$tag2]
        ) {
            // We dont need to add another "Joined" message
            // if the user left/returned before the system had a change to register
            return;
        }

        $this->em->beginTransaction();
        try {
            $convo->addParticipant($person);
            $this->em->persist($convo);

            $this->addSystemMessage(
                $convo,
                'message_user-joined',
                ['name'        => $person->display_name_user],
                ['user_joined' => true, 'person_name' => $person->display_name_user, 'person_id' => $person->id]
            );

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * @param ChatConversation $convo
     * @param Person           $who
     */
    public function personLeft(ChatConversation $convo, Person $person)
    {
        $tag1 = 'user_joined.'.$person->getId();
        $tag2 = 'user_left.'.$person->getId();

        $joined_left_counts = App::getDb()->fetchAllKeyValue('
            SELECT tag, COUNT(*)
            FROM chat_messages
            WHERE conversation_id = ? AND tag IN (?, ?)
            GROUP BY tag
        ', [$convo->getId(), $tag1, $tag2]);

        if (
            $joined_left_counts
            && isset($joined_left_counts[$tag1])
            && isset($joined_left_counts[$tag2])
            && $joined_left_counts[$tag1] != $joined_left_counts[$tag2]
        ) {
            // We dont need to add another "Left" message
            // if the user left/returned before the system had a change to register
            return;
        }

        $this->em->beginTransaction();
        try {
            $convo->removeParticipant($person);
            $this->em->persist($convo);

            $this->addSystemMessage(
                $convo,
                'message_user-left',
                ['name'      => $person->display_name_user],
                ['user_left' => true, 'person_name' => $person->display_name_user, 'person_id' => $person->id]
            );

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Change the department of a chat.
     *
     *
     * @param ChatConversation                            $convo
     * @param \Application\DeskPRO\Entity\Department|null $dep
     * @param Person                                      $who
     *
     * @throws \Exception
     *
     * @return
     */
    public function setDepartment(ChatConversation $convo, Department $dep = null, Person $who)
    {
        // Already the departmetn
        if (!$dep && !$convo->department) {
            return;
        }
        if ($dep && $convo->department && $convo->department->id == $dep->id) {
            return;
        }

        $old_dep_id = $convo->department_id;

        $this->em->beginTransaction();
        try {
            $convo->department = $dep;
            $this->em->persist($convo);

            if ($dep) {
                $dep_name = $dep->full_title;
            } else {
                $dep_name = $this->tr->phrase('agent.general.none');
            }
            $this->addSystemMessage(
                $convo,
                'message_set-department',
                ['name'               => $who->display_name_user, 'department' => $dep_name],
                ['department_changed' => true, 'new_department_id' => $convo->department_id]
            );

            $this->dispatchLegacyEvent(
                'chat.depchange',
                array_merge($convo->getInfo(), ['old_department_id' => $old_dep_id])
            );

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Assigns a chat to an agent.
     *
     * @param ChatConversation $convo
     * @param Person           $agent
     *
     * @throws \Exception
     */
    public function assignAgent(ChatConversation $convo, Person $agent)
    {
        if (!$agent->is_agent) {
            throw new \InvalidArgumentException("Person `{$agent->id}` is not an agent");
        }

        // Already assigned to that agent
        if ($convo->getAgent() && $convo->getAgent()->id == $agent->id) {
            return;
        }

        $old_agent_id   = $convo->agent_id;
        $old_agent_name = '';

        if ($convo->agent) {
            $old_agent_name = $convo->agent->getDisplayNameUser();
        }

        $this->em->beginTransaction();
        try {
            $convo->agent = $agent;
            $this->em->persist($convo);

            $this->sendMessageAssignEvent($convo, $old_agent_id, $old_agent_name);

            $this->em->flush();

            $this->dispatchLegacyEvent(
                'chat.reassigned',
                array_merge(
                    $convo->getInfo(),
                    ['old_agent_id' => $old_agent_id, 'new_agent_name' => $agent->display_name_user]
                )
            );

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * @param ChatConversation $conversation
     * @param null             $old_agent_id
     * @param string           $old_agent_name
     */
    public function sendMessageAssignEvent(ChatConversation $conversation, $old_agent_id = null, $old_agent_name = '')
    {
        $agent = $conversation->getAgent();
        $this->addSystemMessage($conversation, 'message_assigned', ['name' => $agent->display_name_user], [
            'chat_assigned'     => true,
            'assigned_to'       => $agent->id,
            'assigned_name'     => $agent->getDisplayNameUser(),
            'assigned_avatar'   => $agent->getPictureUrl(16),
            'old_assigned_to'   => $old_agent_id,
            'old_assigned_name' => $old_agent_name,
        ]);
    }

    /**
     * Add a new user track (the page theyre viewing) message.
     *
     * @param ChatConversation $convo
     * @param string           $url
     */
    public function addUserTrack(ChatConversation $convo, $url)
    {
        $url_show = preg_replace('#^https?://(www\.)?#i', '', $url);
        if (strlen($url_show) > 50) {
            $url_show = substr($url_show, 0, 50).'...';
        }

        $url      = htmlspecialchars($url);
        $url_show = htmlspecialchars($url_show);

        $label = "<a href=\"$url\" target=\"_blank\" title=\"$url\">$url_show</a>";

        $this->addSystemMessage($convo, 'msg_new_user_track', ['label' => $label], [
            'new_user_track' => $url,
            'user_hidden'    => true,
            'is_html'        => true,
        ]);
    }

    /**
     * Unassign the chat.
     *
     * @param ChatConversation $convo
     */
    public function unassignAgent(ChatConversation $convo)
    {
        // Already unassigned
        if (!$convo->getAgent()) {
            return;
        }

        $old_agent_id   = $convo->getAgentId();
        $old_agent_name = $convo->getAgent()->getDisplayNameUser();

        $convo->setAgent(null);
        $this->em->persist($convo);

        $this->addSystemMessage($convo, 'message_unassigned', [], [
            'chat_unassigned'   => true,
            'old_assigned_to'   => $old_agent_id,
            'old_assigned_name' => $old_agent_name,
        ]);

        // Try to reassign
        if ($this->auto_assigner) {
            $assign_agent = $this->auto_assigner->getAgent($convo);
            if ($assign_agent) {
                $this->assignAgent($convo, $assign_agent);
            }
        }

        $this->em->flush();

        // If no agent auto-assigned,
        // need to broadcast an alert to other agents
        if (!$convo->getAgent() && $convo->getStatus() == 'open') {
            $this->dispatchLegacyEvent(
                'chat.unassigned',
                array_merge($convo->getInfo(), ['old_agent_id' => $old_agent_id])
            );
        }
    }

    /**
     * Mark an agent as timed out and unassign the chat.
     *
     *
     * @param ChatConversation $convo
     *
     * @throws \Exception
     */
    public function agentTimeout(ChatConversation $convo, Person $person)
    {
        if (!$convo->getAgent()) {
            return;
        }

        $this->em->beginTransaction();
        try {
            $this->addSystemMessage(
                $convo,
                'message_agent-timeout',
                ['name'            => $convo->getAgent()->display_name_user],
                ['agent_timed_out' => true]
            );

            $this->personLeft($convo, $person);

            if ($convo->getAgent() && $convo->getAgent()->getId() == $person->getId()) {
                $this->unassignAgent($convo);
            }

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Mark a user as timed out and end the chat.
     *
     * @param ChatConversation $convo
     */
    public function userTimeout(ChatConversation $convo)
    {
        $this->em->beginTransaction();
        try {
            $this->addSystemMessage(
                $convo,
                'message_user-timeout',
                [],
                ['user_timed_out' => true]
            );
            $this->endChat($convo, null, 'timeout');

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Mark the chat as ended due to a wait timeout.
     *
     * @param ChatConversation $convo
     */
    public function waitTimeout(ChatConversation $convo)
    {
        $this->em->beginTransaction();
        try {
            $this->addSystemMessage(
                $convo,
                'message_wait-timeout',
                [],
                ['wait_timed_out' => true]
            );
            $this->endChat($convo, null, 'wait_timeout');

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * Mark the chat as ended due to a wait timeout.
     *
     * @param ChatConversation $convo
     */
    public function userAbandoned(ChatConversation $convo)
    {
        $this->em->beginTransaction();
        try {
            $this->addSystemMessage(
                $convo,
                'message_ended-by-user',
                [],
                ['user_abandoned' => true]
            );
            $this->endChat($convo, null, 'abandoned');

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    /**
     * @param $reason
     */
    public function endChat(ChatConversation $convo, Person $author = null, $reason = '')
    {
        $convo->status = 'ended';

        if ($author) {
            $convo->ended_by = ChatConversation::ENDED_AGENT;
        } elseif ($reason == 'timeout') {
            $reason          = '';
            $convo->ended_by = ChatConversation::ENDED_TIMEOUT;
        } elseif ($reason == 'wait_timeout') {
            $reason          = '';
            $convo->ended_by = ChatConversation::ENDED_WAIT_TIMEOUT;
        } elseif ($reason == 'abandoned') {
            $reason          = '';
            $convo->ended_by = ChatConversation::ENDED_ABANDONED;
        }

        if ($convo->ended_by != 'timeout' && $convo->ended_by != 'wait_timeout' && $convo->ended_by != 'abandoned') {
            if ($author) {
                $this->addSystemMessage($convo, 'message_ended-by', ['name' => $author->getDisplayNameUser()], ['chat_ended' => true]);
            } else {
                $this->addSystemMessage($convo, 'message_ended', [], ['chat_ended' => true]);
            }
        }

        $this->dispatchLegacyEvent('chat.ended', $convo->getInfo());

        if ($reason !== 'timeout' && $reason !== 'wait_timeout' && $reason != 'abandoned') {
            $this->autoSendChatTranscript($convo);
        }
    }

    /**
     * The user ended the chat.
     *
     *
     * @param ChatConversation $convo
     *
     * @throws \Exception
     */
    public function endChatUser(ChatConversation $convo, $ended_by = null)
    {
        // Already ended
        if ($convo->status == 'ended') {
            return;
        }

        $convo->status = 'ended';

        if ($ended_by) {
            $convo->ended_by = $ended_by;
        }

        $this->addSystemMessage($convo, 'message_ended-by-user', [], ['chat_ended']);

        $this->dispatchLegacyEvent('chat.ended', $convo->getInfo());

        $this->autoSendChatTranscript($convo);
    }

    /**
     * Send a transcript of a chat to a user.
     *
     * @param ChatConversation $convo
     * @param                  $email
     * @param string           $name
     *
     * @deprecated Emails are sent through a WorkerProcess now
     */
    public function sendChatTranscript(ChatConversation $convo, $email, $name = '')
    {
        $convo_messages = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:ChatMessage m
            WHERE m.conversation = ?1 AND m.is_user_hidden = false
            ORDER BY m.id DESC
        ')->setParameter(1, $convo)->execute();

        $vars = [
            'convo'          => $convo,
            'convo_messages' => $convo_messages,
        ];

        $message = App::getMailer()->createMessage();
        $message->setTo($email, $name);
        $message->setTemplate('DeskPRO:emails_user:chat-transcript.html.twig', $vars);

        App::getMailer()->send($message);
    }

    /**
     * Send a chat transcript to the user who started a chat if we have an email for them.
     *
     * @param ChatConversation $convo
     *
     * @return bool
     */
    public function autoSendChatTranscript(ChatConversation $convo)
    {
        if (!$convo->getDateFirstAgentMessage()) {
            return false;
        }

        $email = '';
        if ($convo->getPerson() && $convo->getPerson()->getPrimaryEmailAddress()) {
            $email = $convo->getPerson()->getPrimaryEmailAddress();
        } elseif ($convo->getPersonEmail()) {
            $email = $convo->getPersonEmail();
        }

        if ($email) {
            $convo->setShouldSendTranscript(true);
            App::getOrm()->persist($convo);
            App::getOrm()->flush($convo);

            return true;
        }

        return false;
    }

    /**
     * Add a new message form the user who started the chat.
     *
     * @param ChatConversation $convo
     * @param                  $message
     * @param array            $metadata
     *
     * @return ChatMessage
     */
    public function addUserMessage(ChatConversation $convo, $message, array $metadata = [])
    {
        $person = $this->person;
        if (!$person || !$this->person->id) {
            $person = null;
        }

        $metadata['is_user_message'] = true;

        return $this->addMessage($convo, $person, $message, $metadata);
    }

    /**
     * Add a new message from a user.
     *
     * @param ChatConversation $convo
     * @param Person           $author
     * @param $message
     * @param array $metadata
     *
     * @throws \Exception
     *
     * @return ChatMessage
     */
    public function addMessage(ChatConversation $convo, Person $author = null, $message, array $metadata = [])
    {
        $msg = new ChatMessage();

        if (DP_INTERFACE == 'agent') {
            $msg->origin = 'agent';
        } elseif (DP_INTERFACE == 'user') {
            $msg->origin = 'user';
        }

        if ($author) {
            $msg->author = $author;
        }
        $msg->content = $message;

        if (isset($metadata['user_hidden'])) {
            $msg->is_user_hidden = true;
            unset($metadata['user_hidden']);
        }

        if (isset($metadata['is_html'])) {
            $msg->is_html = true;
            unset($metadata['is_html']);
        }

        if ($author) {
            $metadata['person_avatar']      = $author->getPictureUrl(40);
            $metadata['person_avatar_icon'] = $author->getPictureUrl(16);
        }

        if (isset($metadata['is_html'])) {
            $msg->is_html = (bool) $metadata['is_html'];
            unset($metadata['is_html']);
        }

        $msg->metadata = $metadata;
        $convo->addMessage($msg);

        // If subject less than 250 chars, add message onto it so subject is like a little preview
        if (!$msg->is_user_hidden and !$msg->is_sys and strlen($convo->subject) < 250) {
            if ($convo->subject) {
                $convo->subject .= ' | ';
            }

            $convo->subject .= strip_tags($msg->content);
        }

        $this->em->beginTransaction();
        try {
            $this->em->persist($msg);
            $this->em->persist($convo);
            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $channel = $convo->getChannelId('newmessage');
        if ($msg->is_user_hidden) {
            $channel = $convo->getChannelId('newmessage_hidden');
        }

        $data = $msg->getInfo();

        $this->dispatchLegacyEvent($channel, $data);

        return $msg;
    }

    /**
     * @param ChatConversation $convo
     * @param string           $message_id
     * @param array            $vars
     * @param array            $metadata
     *
     * @throws \Exception
     *
     * @return ChatMessage
     */
    public function addSystemMessage(ChatConversation $convo, $message_id, array $vars = [], $metadata = [])
    {
        $message = $vars;
        \Orb\Util\Arrays::unshiftAssoc($message, 'phrase_id', $message_id);

        // Metadata is used when rendering the phrase in PHP,
        // so add vars to the metadata array
        $metadata              = array_merge($metadata, $vars);
        $metadata['phrase_id'] = $message_id;

        $message = json_encode($message);

        $msg          = new ChatMessage();
        $msg->is_sys  = true;
        $msg->content = $message;

        if (isset($metadata['user_joined']) && isset($metadata['person_id']) && $metadata['person_id']) {
            $msg->tag = 'user_joined.'.$metadata['person_id'];
        } elseif (isset($metadata['user_left']) && isset($metadata['person_id']) && $metadata['person_id']) {
            $msg->tag = 'user_left.'.$metadata['person_id'];
        }

        if (isset($metadata['user_hidden'])) {
            $msg->is_user_hidden = true;
            unset($metadata['user_hidden']);
        }

        if (isset($metadata['is_html'])) {
            $msg->is_html = true;
            unset($metadata['is_html']);
        }

        $msg->metadata = $metadata;

        $convo->addMessage($msg);
        $this->em->beginTransaction();

        try {
            $this->em->persist($msg);
            $this->em->persist($convo);
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }

        $channel = $convo->getChannelId('newmessage');
        if ($msg->is_user_hidden) {
            $channel = $convo->getChannelId('hidden_newmessage');
        }

        $this->em->flush();

        $this->dispatchLegacyEvent($channel, $msg->getInfo());

        return $msg;
    }

    protected function getCurrentClientId()
    {
        if ($this->session) {
            return $this->session->getId();
        }

        return '';
    }

    /**
     * @param ChatConversation $convo
     * @param                  $preview_string
     */
    public function setUserTypingIndicator(ChatConversation $convo, $preview_string)
    {
        $this->dispatchLegacyEvent($convo->getChannelId('usertyping'), ['preview' => $preview_string]);
    }

    /**
     * @param ChatConversation $convo
     * @param array            $message_ids
     */
    public function ackMessages(ChatConversation $convo, array $message_ids)
    {
        if (!$message_ids) {
            return;
        }

        $this->em->beginTransaction();

        try {
            $d = date('Y-m-d H:i:s');
            $this->db->executeUpdate('
                UPDATE chat_messages
                SET date_received = ?
                WHERE
                    id IN (?)
                    AND conversation_id = ?
            ',
                [$d, $message_ids, $convo->getId()],
                [\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY, \PDO::PARAM_INT]
            );

            $this->dispatchLegacyEvent($convo->getChannelId('ack_messages'), ['message_ids' => $message_ids]);

            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();
            throw $e;
        }
    }

    public function getSession()
    {
        return $this->session;
    }

    protected function dispatchLegacyEvent($eventType, $data)
    {
        $this->eventDispatcher->dispatch(LegacySystemEvent::EVENT_NAME, new LegacySystemEvent($eventType, $data));
    }
}
