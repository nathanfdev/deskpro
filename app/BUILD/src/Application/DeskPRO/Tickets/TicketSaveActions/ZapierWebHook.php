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
