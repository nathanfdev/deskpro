<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;
use Application\DeskPRO\Chat\UserChat\GroupingCounter;
use Application\DeskPRO\ClientMessage\Generator\Chat;
use Application\DeskPRO\ClientMessage\Generator\Chat as ChatClientMessageGenerator;
use Application\DeskPRO\CustomFields\Handler\HandlerAbstract;
use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatBlock;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatRoundRobinAgent;
use Application\DeskPRO\Entity\CustomDefChat;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\Searcher\ChatConversationSearch;
use Application\DeskPRO\Searcher\SearcherAbstract;
use DeskPRO\Bundle\AppBundle\Notification\Event\LegacySystemEvent;
use DeskPRO\Bundle\MessengerBundle\Notification\Event\ChatEvent;
use Orb\Util\Dates;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserChatController extends AbstractController
{
    /** @var array */
    protected $filters = ['mine', 'assigned', 'missed'];
    /** @var array */
    protected $groups = ['none', 'department', 'agent', 'date_created', 'total_to_ended'];

    public function viewAction($conversation_id, $action)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$convo || !$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $hasJoined = (bool) $convo->hasParticipant($this->person) || ($convo->getAgentId() == $this->person->getId());

        if (!$hasJoined && $action == 'join') {
            $this->joinConvo($convo);
        }

        $convoMessages = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:ChatMessage m
            WHERE m.conversation = ?1
            ORDER BY m.id DESC
        ')->setParameter(1, $convo)->execute();

        $session = $convo->getSession();

        // For selector
        /** @var PersonRepository $personRepository */
        $personRepository          = $this->em->getRepository(Person::class);
        $availableForChatAgentsIds = $personRepository->getActiveAgentIdsForUserChat();
        $onlineAgentsIds           = $personRepository->getActiveAgents(true);

        $agents = $personRepository->getAgents();
        $me     = $this->person;

        $offlineAgents = array_filter($agents, function ($agent) use ($onlineAgentsIds, $me) {
            /* @var Person $agent */
            return !in_array($agent->getId(), $onlineAgentsIds) && $agent->getId() != $me->getId();
        });
        $availableForChatAgents = array_filter($agents, function ($agent) use ($availableForChatAgentsIds, $me) {
            /* @var Person $agent */
            return in_array($agent->getId(), $availableForChatAgentsIds) && $agent->getId() != $me->getId();
        });
        $onlineAgents = array_filter($agents, function ($agent) use ($onlineAgentsIds, $availableForChatAgentsIds, $me) {
            /* @var Person $agent */
            return in_array($agent->getId(), array_diff($onlineAgentsIds, $availableForChatAgentsIds)) &&
            $agent->getId() != $me->getId();
        });

        $convoApi = [];
        foreach (['id', 'subject', 'person_name', 'person_email', 'status', 'ended_by'] as $key) {
            $convoApi[$key] = $convo->$key;
        }
        if ($convo->getPerson()) {
            $convoApi['person'] = $convo->getPerson()->getDataForWidget();
        }
        if ($convo->getAgent()) {
            $convoApi['agent'] = $convo->getAgent()->getDataForWidget();
        }

        $block = null;

        $fieldManager = $this->container->getSystemService('chat_fields_manager');
        $customFields = $fieldManager->getDisplayArrayForObject($convo);

        return $this->render('AgentBundle:UserChat:view.html.twig', [
            'convo_messages'            => $convoMessages,
            'convo'                     => $convo,
            'convo_api'                 => $convoApi,
            'session'                   => $session,
            'available_for_chat_agents' => $availableForChatAgents,
            'online_agents'             => $onlineAgents,
            'offline_agents'            => $offlineAgents,
            'block'                     => $block,
            '$field_manager'            => $fieldManager,
            'custom_fields'             => $customFields,
            'has_joined'                => $hasJoined,
        ]);
    }

    public function deleteAction(Request $request, $conversation_id)
    {
        if (!$this->person->hasPerm('agent_chat.delete')) {
            throw new AccessDeniedHttpException();
        }

        /** @var ChatConversation $convo */
        if (!$convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id)) {
            throw new NotFoundHttpException();
        }

        $this->em->remove($convo);
        $this->em->flush();

        return $this->createJsonResponse(['success' => true]);
    }

    public function joinChatAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$convo || !$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $hasJoined = (bool) $convo->hasParticipant($this->person) || ($convo->getAgentId() == $this->person->getId());

        $assigned = $this->joinConvo($convo);

        if (!$hasJoined) {
        }

        return $this->createJsonCmResponse([
            'result'   => 'success',
            'assigned' => $assigned,
        ]);
    }

    private function joinConvo($convo)
    {
        $assigned = false;

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager',
            ['session' => $this->session->getEntity()]
        );

        $chatManager->personJoined($convo, $this->person);

        $this->registerActivity();

        if ($convo->status == 'open') {
            if (!$convo['agent']) {
                $assigned = true;
                $chatManager->assignAgent($convo, $this->person);
            }
        }

        return $assigned;
    }

    /**
     * Reassign a chat.
     *
     * @param $conversation_id
     * @param $agent_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     * @throws \Exception
     *
     * @return \Application\DeskPRO\HttpKernel\Controller\Response
     *
     * @internal param $quick_reply_id
     */
    public function assignChatAction($conversation_id, $agent_id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);

        if ($agent_id) {
            $agent = $this->em->find('DeskPRO:Person', $agent_id);
        } else {
            $agent = null;
        }
        if ($agent) {
            $chatManager->assignAgent($convo, $agent);
        } else {
            $chatManager->unassignAgent($convo);
        }

        return $this->createJsonCmResponse();
    }

    public function getGroupByCountsAction()
    {
        $userGroups  = $this->in->getArrayValue('filters');
        $filters     = $this->getFilters();
        $groups      = $this->getGroups();
        $groupCounts = [];

        foreach ($userGroups as $filterId => $groupId) {
            if (!$filterId || !in_array($filterId, $filters)) {
                $filterId = $filters[0];
            }

            if (!$groupId || !in_array($groupId, $groups)) {
                $groupId = $groups[0];
            }

            $searcher = new ChatConversationSearch();
            $searcher->setPersonContext($this->person);
            $searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
            $this->updateSearcherFilter($searcher, $filterId);

            $grouper                = new GroupingCounter($groupId);
            $counts                 = $grouper->getCounts($searcher);
            $groupCounts[$filterId] = $this->renderView('AgentBundle:UserChat:window-filter-groupresult.html.twig',
                ['groups' => $counts, 'filter_id' => $filterId, 'group_by' => $groupId]);
        }

        return $this->createJsonResponse($groupCounts);
    }

    /**
     * Reassign a chat.
     *
     * @param $conversation_id
     * @param $agent_id
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Response
     *
     * @internal param $quick_reply_id
     */
    public function sendInviteAction($conversation_id, $agent_id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $agent = $this->em->find('DeskPRO:Person', $agent_id);

        $eventData           = $convo->getInfo();
        $eventData['target'] = $agent;
        $this->get('event_dispatcher')->dispatch(
            LegacySystemEvent::EVENT_NAME,
            new LegacySystemEvent('chat.invited', $eventData)
        );

        return $this->createJsonResponse(['success' => true]);
    }

    /**
     * Changes properties.
     *
     * @param  $conversation_id
     */
    public function changePropertiesAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);

        $props = $this->in->getCleanValueArray('props', 'raw', 'string');

        if (isset($props['department_id'])) {
            $dep = null;
            if ($props['department_id']) {
                /** @var Department $dep */
                $dep = $this->em->find('DeskPRO:Department', $props['department_id']);
            }
            $chatManager->setDepartment($convo, $dep, $this->person);
        }

        return $this->createJsonCmResponse();
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function saveFieldsAction($conversation_id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        /** @var $fieldManager \Application\DeskPRO\CustomFields\ChatFieldManager */
        $fieldManager = $this->container->getSystemService('chat_fields_manager');
        $data         = $this->in->getCleanValueArray('custom_fields', 'raw', 'raw');
        $trans        = $this->container->getTranslator();
        $errors       = [];

        foreach ($fieldManager->getFields() as $field) {
            /* @var CustomDefChat $field */
            $handlerErrors = $field->getHandler()->validateFormData(
                $data,
                HandlerAbstract::CONTEXT_AGENT
            );
            foreach ($handlerErrors as $code) {
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
                $errors[] = $field['title'].': '.$msg;
            }
        }

        if (!$errors) {
            $fieldManager->saveFormToObject($data, $convo);
        }

        $customFields = $fieldManager->getDisplayArrayForObject($convo);

        return $this->render('AgentBundle:UserChat:view-page-display-holders.html.twig', [
            'convo'         => $convo,
            'custom_fields' => $customFields,
            'errors'        => $errors,
        ]);
    }

    /**
     * Add a participant.
     *
     * @param  $conversation_id
     * @param  $agent_id
     */
    public function addPartAction($conversation_id, $agent_id)
    {
        /** @var ChatConversation $convo */
        $convo           = $this->em->find('DeskPRO:ChatConversation', $conversation_id);
        $eventDispatcher = $this->get('event_dispatcher');

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $agent = $this->em->find(Person::class, $agent_id);
        if (!$agent or $convo->hasParticipant($agent)) {
            return $this->createJsonResponse([]);
        }
        $convo->addParticipant($agent);

        foreach ($convo->getCreatedMessages() as $msg) {
            ChatClientMessageGenerator::createNewMessageMessages($msg, $eventDispatcher);
        }
        ChatClientMessageGenerator::createNewAddedPartMessage($convo, $agent, $eventDispatcher);
        ChatClientMessageGenerator::createPartisipatedUpdatedMessages($convo, $eventDispatcher);

        $this->em->transactional(function ($em) use ($convo) {
            $em->persist($convo);
            $em->flush();
        });

        return $this->createJsonCmResponse([]);
    }

    /**
     * @param $conversation_id
     *
     * @return Response
     */
    public function syncPartsAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo           = $this->em->find('DeskPRO:ChatConversation', $conversation_id);
        $eventDispatcher = $this->get('event_dispatcher');

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw $this->createNotFoundException();
        }

        $have = [];
        foreach ($convo->getParticipants() as $part) {
            if ($convo->getAgentId() == $part->id) {
                continue;
            }
            $have[] = $part->id;
        }

        $target = $this->container->getIn()->getCleanValueArray('agent_ids', 'uint', 'discard');
        $add    = array_diff($target, $have);

        if ($add) {
            foreach ($add as $pid) {
                $agent = $this->em->getRepository(Person::class)->find($pid);
                if (!$agent || !$agent->is_agent) {
                    continue;
                }

                $convo->addParticipant($agent);

                foreach ($convo->getCreatedMessages() as $msg) {
                    ChatClientMessageGenerator::createNewMessageMessages($msg, $eventDispatcher);
                }
                ChatClientMessageGenerator::createNewAddedPartMessage($convo, $agent, $eventDispatcher);
                ChatClientMessageGenerator::createPartisipatedUpdatedMessages($convo, $eventDispatcher);
            }

            $this->em->transactional(function ($em) use ($convo) {
                $em->persist($convo);
                $em->flush();
            });
        }

        return $this->createJsonCmResponse([
            'client_messages' => [],
        ]);
    }

    /**
     * End a chat.
     *
     * @param int $conversation_id
     *
     * @return Response
     */
    public function endChatAction($conversation_id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);
        $chatManager->endChat($convo, $this->person, '');

        return $this->createJsonCmResponse();
    }

    /**
     * Stores datetime of the last agent typing event.
     *
     * @param int $conversation_id
     *
     * @return Response
     */
    public function typingAction($conversation_id)
    {
        $erase = $this->in->getBoolInt('erase');

        /** @var ChatConversation $conversation */
        $conversation = $this->em->find(ChatConversation::class, $conversation_id);
        $date         = new \DateTime();
        $conversation->setDateAgentTyping($erase ? null : $date);

        $type = $erase ? ChatEvent::TYPING_END_EVENT_TYPE : ChatEvent::TYPING_START_EVENT_TYPE;

        $this->get('event_dispatcher')->dispatch(
            ChatEvent::EVENT_NAME, new ChatEvent($conversation_id, $type, [
                'date_typing' => $date,
                'origin'      => 'agent',
            ])
        );

        $this->em->persist($conversation);
        $this->em->flush();

        return $this->createJsonCmResponse();
    }

    /**
     * Accepts a POST of a new message to a conversation.
     *
     * @param int $conversation_id
     *
     * @return Response
     */
    public function sendMessageAction($conversation_id)
    {
        if ($conversation_id instanceof ChatConversation) {
            // sendAgentMessageAction calls this with the convo already
            $convo = $conversation_id;
        } else {
            $convo = $this->em->find(ChatConversation::class, $conversation_id);
        }

        $otherData = [];
        if ($this->in->getString('content')) {
            $metadata = [];
            if ($this->in->getBool('is_html')) {
                $metadata['is_html'] = true;

                $content = Strings::trimHtml($this->in->getHtmlCore('content'));
                $content = Strings::prepareWysiwygHtml($content);

                $this->get('attachment_helper')->processInlineBlobs($content, $this->in->getArrayOfInts('blob_inline_ids'));
            } else {
                $content = $this->in->getString('content');
            }

            /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
            $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);
            $message     = $chatManager->addMessage(
                $convo,
                $this->person,
                $content,
                $metadata
            );

            $otherData['message_id'] = $message->getId();

            // Reset last agent typing time on send message
            $convo->setDateAgentTyping(null);

            $this->registerActivity();

            $this->em->persist($convo);
            $this->em->flush();
        }

        return $this->createJsonCmResponse($otherData);
    }

    /**
     * End a chat.
     *
     * @param int $conversation_id
     *
     * @return Response
     */
    public function leaveChatAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        // agent is not participant of the chat, skipping
        if ($convo->getAgent() !== $this->person && !$convo->hasParticipant($this->person)) {
            return $this->createJsonCmResponse();
        }

        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);
        $chatManager->personLeft($convo, $this->person);

        /* @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        if ($convo->status == 'open') {
            switch ($this->in->getString('action')) {
                case 'unassign':
                    if ($convo->agent && $convo->agent->getId() == $this->person->getId()) {
                        $chatManager->unassignAgent($convo);
                    }
                    break;

                case 'end':
                    $chatManager->endChat($convo, $this->person);
                    break;
            }
        }

        return $this->createJsonCmResponse();
    }

    public function sendFileAction($conversation_id)
    {
        if ($conversation_id instanceof ChatConversation) {
            // sendAgentMessageAction calls this with the convo already
            $convo = $conversation_id;
        } else {
            $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);
        }

        /** @var Blob $blob */
        $blob = $this->em->getRepository('DeskPRO:Blob')->find($this->in->getUint('send_blob_id'));

        if (!$blob) {
            return $this->createJsonCmResponse();
        }

        $msg = "File: <a href=\"{$blob->getDownloadUrl(true)}\" target=\"_blank\">".htmlspecialchars($blob->filename).'</a> ('.$blob->getReadableFilesize().')';
        if ($blob->isImage()) {
            $msg .= '<div class="file-thumb"><img src="'.$blob->getThumbnailUrl(50, true).'" /></div>';
        }

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);
        $blob->setIsTemp(false);
        $chatManager->addMessage(
            $convo,
            $this->person,
            $msg,
            [
                'is_html' => true,
                'type'    => 'file',
                'blob_id' => $blob->id,
                'blob'    => [
                    'blob_id'           => $blob->getId(),
                    'blob_auth'         => $blob->getAuthcode(),
                    'blob_auth_id'      => $blob->getId().'-'.$blob->getAuthcode(),
                    'download_url'      => $blob->getDownloadUrl(true, false),
                    'filename'          => $blob->getFilenameSafe(),
                    'filesize_readable' => $blob->getReadableFilesize(),
                    'is_image'          => $blob->isImage(),
                ],
            ]
        );

        return $this->createJsonCmResponse();
    }

    /**
     * List the articles.
     */
    public function getSectionDataAction()
    {
        $agentNames = $this->em->getRepository('DeskPRO:Person')->getAgentNames();

        $filters = [];
        $tr      = App::getTranslator();

        foreach ($this->getFilters() as $filterId) {
            $searcher = new ChatConversationSearch();
            $searcher->setPersonContext($this->person);
            $searcher->setColumns('COUNT(*)');
            $searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
            $this->updateSearcherFilter($searcher, $filterId);

            $filter       = [];
            $filter['id'] = $filterId;

            $filter['count']      = $this->container->getDbRead('search.filter.chat')->fetchColumn($searcher->getSQL());
            $filter['title']      = $tr->phrase('agent.chat.filter_title_'.$filterId);
            $filter['disallowed'] = implode(',', $this->getDisallowedGroupsForFilter($filterId));
            $filters[]            = $filter;
        }

        $groupers = [];

        foreach ($this->getGroups() as $grouperId) {
            $grouper          = [];
            $grouper['id']    = $grouperId;
            $grouper['title'] = $tr->hasPhrase('agent.general.group_'.$grouperId) ? $tr->hasPhrase('agent.general.group_'.$grouperId) : $grouperId;
            $groupers[]       = $grouper;
        }

        $labelLister = new \Application\DeskPRO\Labels\LabelLister('chat_conversations');
        $index       = $labelLister->getIndexList();

        $labelCounts = $this->em->getRepository('DeskPRO:LabelDef')->getLabelCounts('chat_conversations', 25);
        $cloudGen    = new \Application\DeskPRO\UI\TagCloud($labelCounts);
        $cloud       = $cloudGen->getCloud();

        // Departments
        $departments   = $this->container->getDataService('Department')->getInHierarchy();
        $singleDepMode = false;
        if ($this->em->getRepository('DeskPRO:Department')->countAll() == 1) {
            $singleDepMode = true;
        }

        list($initialCounts, $depCounts) = $this->getCounts();

        $html = $this->renderView('AgentBundle:UserChat:window-section.html.twig', [
            'counts'          => $initialCounts,
            'dep_counts'      => $depCounts,
            'agent_names'     => $agentNames,
            'departments'     => $departments,
            'single_dep_mode' => $singleDepMode,
            'ended_filters'   => $filters,
            'ended_groups'    => $groupers,
            'agent_id'        => $this->getPerson()->id,
            'labels_index'    => $index,
            'labels_cloud'    => $cloud,
        ]);

        return $this->createJsonResponse(['section_html' => $html]);
    }

    public function getOpenCountsAction()
    {
        list($initialCounts, $depCounts) = $this->getCounts();

        return $this->createJsonResponse([
            'counts'     => $initialCounts,
            'dep_counts' => $depCounts,
        ]);
    }

    public function getCounts()
    {
        $searcher = new ChatConversationSearch();
        $searcher->setPersonContext($this->person);
        $searcher->setColumns('IF(agent_id, agent_id, -1) AS agent_id, COUNT(*) AS count');
        $searcher->setGroupBy('chat_conversations.agent_id');
        $searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'open');

        // Initial counts
        $initialCounts           = $this->db->fetchAllKeyValue($searcher->getSql());
        $initialCounts['total']  = array_sum(array_values($initialCounts));
        $initialCounts['active'] = $initialCounts['total'];

        if (isset($initialCounts[-1])) {
            $initialCounts['active'] -= $initialCounts[-1];
        }

        $searcher = new ChatConversationSearch();
        $searcher->setPersonContext($this->person);
        $searcher->setColumns('IF(department_id, department_id, -1) AS department_id, COUNT(*) AS count');
        $searcher->setGroupBy('chat_conversations.department_id');
        $searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'open');
        $searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, 0);

        $depCounts = $this->db->fetchAllKeyValue($searcher->getSql());

        $depCounts['none_total'] = isset($depCounts[-1]) ? $depCounts[-1] : 0;
        $depCounts['none']       = isset($depCounts[-1]) ? $depCounts[-1] : 0;

        $depCounts['0_total'] = $depCounts['none'];

        // Departments
        $departments = $this->container->getDataService('Department')->getInHierarchy();

        foreach ($departments as $dep) {
            $cId   = $dep['id'];
            $total = 0;
            if (isset($depCounts[$cId])) {
                $total = $depCounts[$cId];
            }

            foreach ($dep['children'] as $childDep) {
                $childId                      = $childDep['id'];
                $depCounts[$childId.'_total'] = 0;
                if (isset($depCounts[$childId])) {
                    $depCounts[$childId.'_total'] = $depCounts[$childId];
                    $total += $depCounts[$childId];
                }
            }

            $depCounts["{$cId}_total"] = $total;
            $depCounts['0_total'] += $total;
        }

        return [
            $initialCounts,
            $depCounts,
        ];
    }

    public function listNewChatsAction($department_id)
    {
        $department = 0;
        if ($department_id) {
            if ($department_id == -1) {
                $department = -1;
            } else {
                $department = $this->em->find('DeskPRO:Department', $department_id);
                if (!$department) {
                    $department = 0;
                }
            }
        }

        $convos = $this->em->getRepository('DeskPRO:ChatConversation')->getOpenForAgentAndDepartment(0, $department);

        return $this->render('AgentBundle:UserChat:open-list.html.twig', [
            'convos'        => $convos,
            'department_id' => $department_id,
            'department'    => $department,
            'filter_type'   => 'new',
        ]);
    }

    public function listActiveChatsAction($agent_id)
    {
        $agent = 0;
        if ($agent_id) {
            if ($agent_id == -1) {
                $agent = -1;
            } else {
                $agent = $this->em->find('DeskPRO:Person', $agent_id);
                if (!$agent) {
                    $agent = 0;
                }
            }
        }

        $convos = $this->em->getRepository('DeskPRO:ChatConversation')->getOpenForAgentAndDepartment($agent, -1);

        return $this->render('AgentBundle:UserChat:open-list.html.twig', [
            'agent'       => $agent,
            'agent_id'    => $agent_id,
            'convos'      => $convos,
            'filter_type' => 'active',
        ]);
    }

    /**
     * @param $id
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getChatAlertAction($id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $id);
        if (!$convo) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $tickets = null;
        if ($convo->person) {
            $tickets = $this->em->getRepository('DeskPRO:Ticket')->getLatestByUser($convo->person, 5, true);
        }

        $waitingSecs = time() - $convo->date_created->getTimestamp();

        $url = null;

        return $this->render('AgentBundle:UserChat:chat-alert.html.twig', [
            'convo'        => $convo,
            'person'       => $convo->person,
            'tickets'      => $tickets,
            'session'      => $convo->session,
            'visitor'      => null,
            'waiting_secs' => $waitingSecs,
            'url'          => $url,
        ]);
    }

    /**
     * Creates a JSON response but with client messages as well.
     *
     * @param array $otherData
     *
     * @return Response
     */
    protected function createJsonCmResponse(array $otherData = [])
    {
        $clientMessages               = false;
        $otherData['client_messages'] = $clientMessages;

        return $this->createJsonResponse($otherData);
    }

    /**
     * Lists previously closed chats.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function filterAction($filter_id)
    {
        $filters = $this->getFilters();

        $searcher = new ChatConversationSearch();
        $searcher->setPersonContext($this->person);
        $searcher->setColumns('COUNT(*)');

        $filterParam = $this->in->getString('filter_param');

        if ($filter_id == 'label' && $filterParam) {
            $searcher->addTerm(ChatConversationSearch::TERM_LABEL, SearcherAbstract::OP_IS, $filterParam);
        } else {
            if (!$filter_id || !in_array($filter_id, $filters)) {
                $filter_id = $filters[0];
            }

            $searcher->addTerm(ChatConversationSearch::TERM_STATUS, SearcherAbstract::OP_IS, 'ended');
            $this->updateSearcherFilter($searcher, $filter_id);
        }

        $groups  = $this->getGroups();
        $groupBy = $this->in->getString('group_var');
        $groupId = '';

        if ($groupBy && in_array($groupBy, $groups)) {
            switch ($groupBy) {
                case 'agent':
                    $groupId = $this->in->getInt('group_val');
                    $searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, $groupId);
                    break;
                case 'date_created':
                    $groupId   = $this->in->getString('group_val');
                    $monthYear = explode('-', $groupId, 2);

                    if (count($monthYear) != 2) {
                        break;
                    }

                    $month = (int) $monthYear[0];
                    $year  = (int) $monthYear[1];

                    if (!checkdate($month, 1, $year)) {
                        break;
                    }

                    $beginning = Dates::firstDayInMonth($month, $year);
                    $end       = Dates::lastDayInMonth($month, $year);
                    $searcher->addTerm(ChatConversationSearch::TERM_DATE_CREATED, SearcherAbstract::OP_BETWEEN, ['date1' => $beginning, 'date2' => $end]);

                    break;
                case 'department':
                    $groupId = $this->in->getInt('group_val');
                    $searcher->addTerm(ChatConversationSearch::TERM_DEPARTMENT_ID, SearcherAbstract::OP_IS, $groupId);
                    break;
                case 'total_to_ended':
                    $groupId = $this->in->getInt('group_val');
                    $searcher->addTerm(ChatConversationSearch::TERM_TOTAL_TO_ENDED, SearcherAbstract::OP_IS, $groupId);
                    break;
            }
        }

        $total = $this->container->getDbRead('search.filter.chat')->fetchColumn($searcher->getSql());

        $limit   = 50;
        $maxPage = ceil($total / $limit);

        $page = $this->in->getUint('p');
        if (!$page || $page > $maxPage) {
            $page = 1;
        }

        $start = ($page - 1) * $limit;

        $searcher->setColumns('id');
        $searcher->setLimit('start', $start);
        $searcher->setLimit('limit', $limit);
        $chatIds = $this->container->getDbRead('search.filter.chat')->fetchAllCol($searcher->getSql());

        $chats = $this->container->getEm()->getRepository('DeskPRO:ChatConversation')->getByIds($chatIds, true);

        return $this->render('AgentBundle:UserChat:list.html.twig', [
            'chat_ids'     => $chatIds,
            'chats'        => $chats,
            'total'        => $total,
            'page'         => $page,
            'max_page'     => $maxPage,
            'filter_id'    => $filter_id,
            'filter_param' => $filterParam,
            'group_var'    => $groupBy,
            'group_val'    => $groupId,
        ]);
    }

    public function blockUserAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$convo || !$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        /** @var $chatManager \Application\DeskPRO\Chat\UserChat\UserChatManager */
        $chatManager = $this->container->getSystemObject('user_chat_manager', ['session' => $this->session->getEntity()]);

        if ($session = $convo->getSession()) {
            $block = new ChatBlock();
            $block->setVisitorId($session->getVisitorId());
            $block->by_person = $this->person;
            $block->reason    = $this->in->getString('reason');

            if ($this->in->getBool('block_ip') && $session->getIpAddress()) {
                $block->setIpAddress($session->getIpAddress());
            }

            $this->em->persist($block);
            $this->em->flush();
        }

        if ($convo->status == 'open') {
            $chatManager->endChat($convo, $this->person, '');
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function unblockUserAction($conversation_id)
    {
        /** @var ChatConversation $convo */
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$convo || !$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $session = $convo->getSession();
        if ($session && $session->getVisitorId()) {
            /** @var \Application\DeskPRO\EntityRepository\ChatBlock $rep */
            $rep = $this->em->getRepository('DeskPRO:ChatBlock');
            if ($block = $rep->getBlockForVisitor($session->getVisitorId())) {
                $this->em->remove($block);
                $this->em->flush();
            }
        }

        return $this->createJsonResponse(['success' => true]);
    }

    public function ajaxSaveLabelsAction($conversation_id)
    {
        $convo = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        if (!$convo || !$this->person->getPermissionsManager()->ChatChecker->canView($convo)) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
        }

        $labels = $this->in->getCleanValueArray('labels', 'string', 'discard');

        $convo->getLabelManager()->setLabelsArray($labels);

        $this->em->persist($convo);
        $this->em->flush();

        return $this->createJsonResponse(['success' => 1]);
    }

    protected function updateSearcherFilter($searcher, $filter)
    {
        switch ($filter) {
            case 'mine':
                $searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, $this->person['id']);
                break;

            case 'assigned':
                $searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_NOT, 0);
                break;

            case 'missed':
                $searcher->addTerm(ChatConversationSearch::TERM_AGENT_ID, SearcherAbstract::OP_IS, 0);
                break;
        }
    }

    protected function registerActivity()
    {
        //Register activity to round robins
        $rras = $this->em->getRepository(ChatRoundRobinAgent::class)->findBy(['agent' => $this->getPerson()]);
        foreach ($rras as $rra) {
            /* @var $rra ChatRoundRobinAgent */
            $rra->setLastActivity();
            $this->em->persist($rra);
        }
    }

    protected function getGroups()
    {
        $groups = [];

        foreach ($this->groups as $group) {
            if ($group == 'agent'
                && !$this->person->hasPerm('agent_tickets.view_others')
                && !$this->person->hasPerm('agent_tickets.view_unassigned')
            ) {
                continue;
            }

            $groups[] = $group;
        }

        return $groups;
    }

    protected function getDisallowedGroupsForFilter($filter)
    {
        $disallowed = [];

        if ($filter == 'mine') {
            $disallowed[] = 'agent';
        }

        return $disallowed;
    }

    protected function getFilters()
    {
        $filters = [];

        foreach ($this->filters as $filter) {
            if ($filter == 'assigned' && !$this->person->hasPerm('agent_chat.view_others') && !$this->person->hasPerm('agent_chat.view_unassigned')) {
                continue;
            }

            $filters[] = $filter;
        }

        return $filters;
    }
}
