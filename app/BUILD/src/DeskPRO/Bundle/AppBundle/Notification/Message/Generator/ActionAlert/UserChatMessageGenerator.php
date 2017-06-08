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

namespace DeskPRO\Bundle\AppBundle\Notification\Message\Generator\ActionAlert;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;
use DeskPRO\Bundle\AppBundle\Notification\Message\MessageInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine as TemplatingEngine;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserChatMessageGenerator.
 */
class UserChatMessageGenerator extends SystemEventGenerator
{
    /**
     * @var TemplatingEngine
     */
    private $templating;

    public function __construct(
        EntityManager $em,
        TokenStorageInterface $token_storage,
        AgentDataService $agentDataService,
        TemplatingEngine $templating
    ) {
        parent::__construct($em, $token_storage, $agentDataService);
        $this->templating = $templating;
    }

    /**
     * @param SystemEventInterface $event
     *
     * @return MessageInterface[]
     */
    public function createMessages(SystemEventInterface $event)
    {
        $event->getName();
        /* @var UserChatEvent $event */
        $messages = [];
        foreach ($this->getTarget($event) as $agent) {
            $messages[] = new ActionAlert((int) $agent, $this->getData($event), $event->getName());
        }

        return $messages;
    }

    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof UserChatEvent;
    }

    private function getData(UserChatEvent $event)
    {
        $data = $event->getData();
        if ($event->getEventType() === ClientMessageEvent::CHANNEL_CHAT_NEW) {
            $convo = $this->em->find(ChatConversation::class, $data['conversation_id']);
            if (!$convo) {
                throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException();
            }

            $tickets = null;
            if ($convo->person) {
                $tickets = $this->em->getRepository('DeskPRO:Ticket')->getLatestByUser($convo->person, 5, true);
            }

            $waiting_secs = time() - $convo->date_created->getTimestamp();

            $url = null;

            $data['html'] = $this->templating->render('AgentBundle:UserChat:chat-alert.html.twig', [
                'convo'        => $convo,
                'person'       => $convo->person,
                'tickets'      => $tickets,
                'session'      => $convo->session,
                'visitor_id'   => $convo->visitor_id,
                'waiting_secs' => $waiting_secs,
                'url'          => $url,
            ]);
        }

        return $data;
    }
}
