<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Bundle\AppBundle\Entity\Zapier\TicketUpdate;
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
            $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'ticket_created']);

            if (!$hooks) {
                return false;
            }

            $httpClient = new HttpClient(['timeout' => 30]);

            $serializationContext = new SideloadSerializationContext();
            $serializationContext->setInlineSideloads(true);

            $options['body'] = $this->serializer->serialize($ticket, 'json', $serializationContext);

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
        } elseif ($state->hasNewUserReply() || $state->hasNewAgentReply()) {
            $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'new_ticket_reply']);

            if (!$hooks) {
                return false;
            }

            $httpClient = new HttpClient(['timeout' => 30]);

            $serializationContext = new SideloadSerializationContext();
            $serializationContext->setInlineSideloads(true);

            $ticketMessage = $ticket->getLastReply();

            if ($ticketMessage) {
                $options['body'] = $this->serializer->serialize($ticketMessage, 'json', $serializationContext);

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
        } elseif (!$state->isTrivialChangeSet()) {
            $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'ticket_update']);

            if (false && !$hooks) {
                return false;
            }

            $httpClient = new HttpClient(['timeout' => 30]);

            $state   = $ticket->getStateChangeRecorder();
            $changes = $state->getChanges();

            $ticketLogs   = [];
            $logGenerator = new TicketLogGenerator($ticket, $context);
            foreach ($changes as $change) {
                $logData = $logGenerator->getLogDataForChange($change);
                if ($logData) {
                    $ticketLogs[] = $logData;
                }
            }

            $serializationContext = new SideloadSerializationContext();
            $serializationContext->setInlineSideloads(true);

            $ticketUpdate = new TicketUpdate();
            $ticketUpdate->setPerformer($context->getPersonContext());
            $ticketUpdate->setChanges($ticketLogs);
            $ticketUpdate->setTicket($ticket);

            $options['body'] = $this->serializer->serialize($ticketUpdate, 'json', $serializationContext);

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
