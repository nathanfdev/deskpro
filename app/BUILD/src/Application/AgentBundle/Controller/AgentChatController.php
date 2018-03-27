<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Controller;

use Application\DeskPRO\App;

/**
 * Handles ticket searches.
 */
class AgentChatController extends AbstractController
{
    /**
     * @var \Application\DeskPRO\Chat\AgentChat
     */
    protected $agent_chat;

    public function init()
    {
        parent::init();

        $this->agent_chat = new \Application\DeskPRO\Chat\AgentChat($this->person, $this->session->getEntity());
    }

    /**
     * Accepts a POST of a new message to a conversation.
     */
    public function sendMessageAction($conversation_id)
    {
        $info = $this->agent_chat->sendMessage($this->in->getString('content'), $conversation_id);

        return $this->createJsonResponse([
            'conversation_id' => $info['conversation']['id'],
            'new_message_id'  => $info['chat_message']['id'],
        ]);
    }

    /**
     * Sending an agent message is less formal in that we automatically
     * create conversations based on time, instead of having
     * chats created first.
     *
     * @param int $convo_id
     *
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @internal param string $agent_id One or more agent ID's
     */
    public function sendAgentMessageAction($convo_id = 0)
    {
        $agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');

        $info = $this->agent_chat->sendAgentMessage($this->in->getString('content'), $agent_ids, $convo_id);

        $date = clone $info['new_message']['date_created'];
        $date->setTimeZone(App::getCurrentPerson()->getDateTimezone());
        $time = App::getContainer()->getTranslator()->date('g:ia', $date, 'agent.time');

        return $this->createJsonResponse([
            'conversation_id' => $info['conversation']['id'],
            'new_message_id'  => $info['new_message']['id'],
            'time'            => $time,
        ]);
    }

    public function getOnlineAgentsAction()
    {
        $cutoff = date('Y-m-d H:i:s', time() - $this->container->getSetting('core_chat.agent_timeout'));

        $agent_info    = [];
        $online_agents = [];

        $agents = $this->em->getRepository('DeskPRO:Person')->getAgents();

        foreach ($agents as $agent) {
            $agent_info[$agent['id']] = [
                'agent_id'            => $agent['id'],
                'agent_name'          => $agent['display_name'],
                'agent_short_name'    => $agent->getDisplayContactShort(4),
                'picture_url'         => $agent->getPictureUrl(10),
                'picture_url_sizable' => $agent->getPictureUrl('{SIZE}'),
            ];
        }

        $sessions = $this->em->createQuery('
            SELECT s,p
            FROM DeskPRO:Session s
            JOIN s.person p
            WHERE p.is_agent = true AND s.date_last > ?1
            GROUP BY p.id
            ORDER BY s.id DESC
        ')->setParameter(1, $cutoff)->execute();

        foreach ($sessions as $sess) {
            $online_agents[] = [
                'agent_id'         => $sess->person['id'],
                'agent_name'       => $sess->person['display_name'],
                'agent_short_name' => $sess->person->getDisplayContactShort(4),
                'picture_url'      => $sess->person->getPictureUrl(10),
            ];
        }

        return $this->createJsonResponse([
            'agent_info'    => $agent_info,
            'online_agents' => $online_agents,
        ]);
    }

    /**
     * Loads messages from the last conversation with agents.
     */
    public function loadConvoMessagesAction()
    {
        $agent_ids = $this->in->getCleanValueArray('agent_ids', 'uint', 'discard');
        $date_cut  = new \DateTime('-5 hours');

        $find_agent_ids   = $agent_ids;
        $find_agent_ids[] = $this->person['id'];

        $conversation = App::getEntityRepository('DeskPRO:ChatConversation')->getRecentForPeople($find_agent_ids, $date_cut);
        if (!$conversation) {
            return $this->createJsonResponse([
                'messages' => [],
            ]);
        }

        $messages = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:ChatMessage m
            WHERE m.conversation = ?0
            ORDER BY m.id ASC
        ')->setParameters([$conversation])->execute();

        $data                    = [];
        $data['conversation_id'] = $conversation->getId();
        $data['messages']        = [];
        foreach ($messages as $message) {
            $date = clone $message->date_created;
            $date->setTimezone($this->person->getDateTimezone());
            $data['messages'][] = [
                'id'       => $message->id,
                'agent_id' => $message->author ? $message->author->id : 0,
                'message'  => $message->content,
                'time'     => $date->format($this->settings->get('core.date_time')),
            ];
        }

        return $this->createJsonResponse($data);
    }

    //###########################################################################
    // List old chats
    //###########################################################################

    /**
     * List the articles.
     */
    public function getSectionDataAction()
    {
        $agent_chatted      = $this->em->getRepository('DeskPRO:ChatConversation')->getAgentList($this->person);
        $agent_team_chatted = $this->em->getRepository('DeskPRO:ChatConversation')->getAgentTeamList($this->person);

        $agent_chatted_counts    = $this->em->getRepository('DeskPRO:ChatConversation')->getConvoCountsBetween($this->person, array_keys($agent_chatted));
        $agent_chatted_counts[0] = array_sum($agent_chatted_counts);

        $agent_team_chatted_counts    = $this->em->getRepository('DeskPRO:ChatConversation')->getTeamConvoCounts($this->person);
        $agent_team_chatted_counts[0] = array_sum($agent_team_chatted_counts);

        $html = $this->renderView('AgentBundle:AgentChat:window-section.html.twig', [
            'agent_chatted'       => $agent_chatted,
            'chatted_counts'      => $agent_chatted_counts,
            'agent_team_chatted'  => $agent_team_chatted,
            'chatted_team_counts' => $agent_team_chatted_counts,
        ]);

        return $this->createJsonResponse(['section_html' => $html]);
    }

    public function agentHistoryAction($agent_id)
    {
        if ($agent_id) {
            $agent         = $this->em->find('DeskPRO:Person', $agent_id);
            $conversations = $this->em->getRepository('DeskPRO:ChatConversation')->getChatsForPeople([
                $this->person['id'],
                $agent['id'],
            ]);
        } else {
            $agent         = null;
            $conversations = $this->em->getRepository('DeskPRO:ChatConversation')->getAgentChatsForPerson($this->person);
        }

        $tpl = 'AgentBundle:AgentChat:list.html.twig';
        if ($this->in->getBool('partial')) {
            $tpl = 'AgentBundle:AgentChat:list-part.html.twig';
        }

        return $this->render($tpl, [
            'agent'         => $agent,
            'conversations' => $conversations,
        ]);
    }

    public function agentTeamHistoryAction($agent_team_id)
    {
        if ($agent_team_id) {
            $agent_team    = $this->em->find('DeskPRO:AgentTeam', $agent_team_id);
            $conversations = $this->em->getRepository('DeskPRO:ChatConversation')->getTeamChatsForPerson($this->person, $agent_team);
        } else {
            $agent_team    = null;
            $conversations = $this->em->getRepository('DeskPRO:ChatConversation')->getTeamChatsForPerson($this->person, null);
        }

        $tpl = 'AgentBundle:AgentChat:list-team.html.twig';
        if ($this->in->getBool('partial')) {
            $tpl = 'AgentBundle:AgentChat:list-team-part.html.twig';
        }

        return $this->render($tpl, [
            'agent_team'    => $agent_team,
            'conversations' => $conversations,
        ]);
    }

    public function agentChatTranscriptAction($conversation_id)
    {
        $conversation = $this->em->find('DeskPRO:ChatConversation', $conversation_id);

        $convo_messages = $this->em->createQuery('
            SELECT m
            FROM DeskPRO:ChatMessage m
            WHERE m.conversation = ?1
            ORDER BY m.id DESC
        ')->setParameter(1, $conversation)->execute();

        return $this->render('AgentBundle:AgentChat:view.html.twig', [
            'convo_messages' => $convo_messages,
            'convo'          => $conversation,
        ]);
    }
}
