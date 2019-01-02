<?php

namespace Application\DeskPRO\Tickets\TicketSaveActions;

use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\TicketLog\TicketLogGenerator;
use DeskPRO\Bundle\AppBundle\Entity\Zapier\TicketUpdate;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
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
        try {
            $state = $ticket->getStateChangeRecorder();

            $sideloadContext = new SideloadSerializationContext();
            $sideloadContext->setInlineSideloads(true);
            $sideloadContext->setIncludes(['person', 'brand', 'ticket']);

            $httpClient = new HttpClient(['timeout' => 30]);

            $autoProcessTicket = $ticket->__dp_last_process_save;

            if ($state->isNewTicket()) {
                $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'ticket_created']);

                if (!$hooks) {
                    return false;
                }

                $options['body'] = \GuzzleHttp\json_encode($this->serializer->toArray(new ApiWrapper($ticket), $sideloadContext));

                /** @var ZapierHook $zapierHook */
                foreach ($hooks as $zapierHook) {
                    try {
                        $params = $zapierHook->getParams();
                        if (!empty($params['filter'])) {
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
                            $context->getLogger()->info('[Zapier] - Webhook does not exists anymore on Zapier we deleted it.');
                        } else {
                            $context->getLogger()->error(sprintf('[Zapier] Exception in webhook ticket_created #%d: [%s] %s', $zapierHook->getId(), $e->getCode(), $e->getMessage()), ['exception' => $e]);
                        }
                    }
                }
            } elseif ($state->hasNewUserReply() || $state->hasNewAgentReply()) {
                $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'new_ticket_reply']);

                if (!$hooks) {
                    return false;
                }

                $ticketMessage = $ticket->getLastReply();

                if ($ticketMessage) {
                    $options['body'] = \GuzzleHttp\json_encode($this->serializer->toArray(new ApiWrapper($ticketMessage), $sideloadContext));

                    /** @var ZapierHook $zapierHook */
                    foreach ($hooks as $zapierHook) {
                        try {
                            $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);
                        } catch (ClientException $e) {
                            // Hooks needs to be unsubscribe
                            if ($e->getCode() === 410) {
                                $this->em->remove($zapierHook);
                                $context->getLogger()->info('[Zapier] - Webhook does not exists anymore on Zapier we deleted it.');
                            } else {
                                $context->getLogger()->error(sprintf('[Zapier] Exception in webhook new_ticket_reply #%d: [%s] %s', $zapierHook->getId(), $e->getCode(), $e->getMessage()), ['exception' => $e]);
                            }
                        }
                    }
                }
            } elseif (!$state->isTrivialChangeSet()) {
                $hooks = $this->em->getRepository(ZapierHook::class)->findBy(['event' => 'ticket_update']);

                if (!$hooks) {
                    return false;
                }

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

                $ticketUpdate = new TicketUpdate();
                $ticketUpdate->setPerformer($context->getPersonContext());
                $ticketUpdate->setChanges($ticketLogs);
                $ticketUpdate->setTicket($ticket);

                $options['body'] = \GuzzleHttp\json_encode($this->serializer->toArray(new ApiWrapper($ticketUpdate), $sideloadContext));

                /** @var ZapierHook $zapierHook */
                foreach ($hooks as $zapierHook) {
                    try {
                        $httpClient->request('POST', $zapierHook->getTargetUrl(), $options);
                    } catch (ClientException $e) {
                        // Hooks needs to be unsubscribe
                        if ($e->getCode() === 410) {
                            $this->em->remove($zapierHook);
                            $context->getLogger()->info('[Zapier] - Webhook does not exists anymore on Zapier we deleted it.');
                        } else {
                            $context->getLogger()->error(sprintf('[Zapier] Exception in webhook ticket_update #%d: [%s] %s', $zapierHook->getId(), $e->getCode(), $e->getMessage()), ['exception' => $e]);
                        }
                    }
                }
            }

            if ($autoProcessTicket === false) {
                $ticket->disableAutoTicketProcess();
            }

            return true;
        } catch (\Throwable $t) {
            $context->getLogger()->error(sprintf('[Zapier] Exception in webhook : [%s] %s', $t->getCode(), $t->getMessage()), ['exception' => $t]);

            return false;
        }
    }
}
