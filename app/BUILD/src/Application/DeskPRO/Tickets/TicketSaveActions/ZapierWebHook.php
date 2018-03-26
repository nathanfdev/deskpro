<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\AppBundle\Util\HttpClient;
use Doctrine\ORM\EntityManager;
use GuzzleHttp\Exception\ClientException;
use JMS\Serializer\Serializer;

class ZapierWebHook implements TicketSaveActionInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function __construct(EntityManager $em, Serializer $serializer)
    {
        $this->em         = $em;
        $this->serializer = $serializer;
    }

    /**
     * {@inheritdoc}
     */
    public function processTicket(Ticket $ticket, ExecutorContextInterface $context)
    {
        $state = $ticket->getStateChangeRecorder();

        if ($state->isNewTicket()) {
            $httpClient = new HttpClient(['timeout' => 30]);

            $serializationContext = new SideloadSerializationContext();

            $options['body'] = $this->serializer->serialize($ticket, 'json', $serializationContext);

            $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'ticket_created']);

            /** @var ZapierHook $zapierHook */
            foreach ($hooks as $zapierHook) {
                try {
                    $params = $zapierHook->getParams();
                    if ($params['filter']) {
                        /** @var LegacyTicketFilter $filter */
                        $filter = $this->em->getRepository(LegacyTicketFilter::class)->find($params['filter']);
                        if ($filter) {
                            $ticketSearch = $filter->getSearcher();
                            $ticketSearch->setPersonContext($zapierHook->getPerson());
                            if (!$ticketSearch->doesTicketMatch($ticket)) {
                                continue;
                            }
                        }
                    }
                    $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);
                } catch (ClientException $e) {
                    // Hooks needs to be unsubscribe
                    if ($e->getCode() === 410) {
                        $this->em->remove($zapierHook);
                    }
                }
            }
        }

        if ($state->hasNewUserReply() || $state->hasNewAgentReply()) {
            $httpClient = new HttpClient(['timeout' => 30]);

            $serializationContext = new SideloadSerializationContext();

            $ticketMessage = $ticket->getLastReply();

            if ($ticketMessage) {
                $options['body'] = $this->serializer->serialize($ticketMessage, 'json', $serializationContext);

                $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'new_ticket_reply']);

                /** @var ZapierHook $zapierHook */
                foreach ($hooks as $zapierHook) {
                    try {
                        $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);
                    } catch (ClientException $e) {
                        // Hooks needs to be unsubscribe
                        if ($e->getCode() === 410) {
                            $this->em->remove($zapierHook);
                        }
                    }
                }
            }
        }
    }
}
