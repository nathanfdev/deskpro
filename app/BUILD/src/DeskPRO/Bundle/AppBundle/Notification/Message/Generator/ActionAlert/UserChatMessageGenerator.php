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
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\AppBundle\DataService\AgentDataService;
use DeskPRO\Bundle\AppBundle\EventListener\ClientMessage\ClientMessageEvent;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\AppBundle\Notification\Event\SystemEventInterface;
use DeskPRO\Bundle\AppBundle\Notification\Event\UserChat\UserChatEvent;
use DeskPRO\Bundle\AppBundle\Notification\Message\ActionAlert;
use DeskPRO\Bundle\AppBundle\Notification\Message\Generator\SystemEventGenerator;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine as TemplatingEngine;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * Constructor.
     *
     * @param EntityManager         $em
     * @param TokenStorageInterface $tokenStorage
     * @param AgentDataService      $agentDataService
     * @param TemplatingEngine      $templating
     * @param LanguageManager       $languageManager
     */
    public function __construct(
        EntityManager         $em,
        TokenStorageInterface $tokenStorage,
        AgentDataService      $agentDataService,
        TemplatingEngine      $templating,
        LanguageManager       $languageManager
    ) {
        parent::__construct($em, $tokenStorage, $agentDataService);

        $this->templating      = $templating;
        $this->languageManager = $languageManager;
    }

    /**
     * {@inheritdoc}
     *
     * @var UserChatEvent
     */
    public function createMessages(SystemEventInterface $event)
    {
        $messages = [];
        foreach ($this->getTarget($event) as $agentId) {
            $agent = $this->em->find(Person::class, $agentId);
            if ($agent) {
                $messages[] = new ActionAlert((int) $agentId, $this->getData($event, $agent), $event->getName());
            }
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateMessage(SystemEventInterface $event)
    {
        return $event instanceof UserChatEvent;
    }

    /**
     * @param UserChatEvent $event
     * @param Person        $agent
     *
     * @return array
     */
    private function getData(UserChatEvent $event, Person $agent)
    {
        $data = $event->getData();
        if ($event->getEventType() === ClientMessageEvent::CHANNEL_CHAT_NEW) {
            $convo = $this->em->find(ChatConversation::class, $data['conversation_id']);
            if (!$convo) {
                throw new NotFoundHttpException();
            }

            if ($convo->getPerson()) {
                /** @var \Application\DeskPRO\EntityRepository\Ticket $ticketRepo */
                $ticketRepo = $this->em->getRepository(Ticket::class);
                $tickets    = $ticketRepo->getLatestByUser($convo->getPerson(), 5, true);
            } else {
                $tickets = null;
            }

            $this->languageManager->callWithLanguage($agent->getLanguage(), function () use (&$data, $convo, $tickets) {
                $data['html'] = $this->templating->render('AgentBundle:UserChat:chat-alert.html.twig', [
                    'convo'        => $convo,
                    'person'       => $convo->getPerson(),
                    'tickets'      => $tickets,
                    'session'      => $convo->getSession(),
                    'visitor_id'   => $convo->getVisitorId(),
                    'waiting_secs' => time() - $convo->getDateCreated()->getTimestamp(),
                    'url'          => null,
                ]);
            });
        }

        return $data;
    }
}
