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

namespace DeskPRO\Bundle\AppBundle\Webhooks;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use Application\DeskPRO\Tickets\Filters\LegacyTermsTransformer;
use Application\DeskPRO\Tickets\TicketManager;
use DeskPRO\Bundle\AppBundle\Entity\Webhooks\TicketWebhook;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\ExecutorContextEnv;
use DeskPRO\Bundle\AppBundle\Webhooks\TicketWebhookVars\SearchTermVars;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TicketWebhookExecutor implements ContainerAwareInterface
{
    /** @var PayloadConvertersRegistry */
    private $convertersRegistry;

    /** @var TicketManager */
    private $ticketManager;

    /** @var ContainerInterface */
    private $container;

    /** @var SearchTermsAliasResolver */
    private $searchTermsAliasResolver;

    /**
     * TicketWebhookExecutor constructor.
     *
     * @param PayloadConvertersRegistry $convertersRegistry
     * @param TicketManager             $ticketManager
     */
    public function __construct(PayloadConvertersRegistry $convertersRegistry, TicketManager $ticketManager)
    {
        $this->convertersRegistry = $convertersRegistry;
        $this->ticketManager      = $ticketManager;
    }

    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        if ($container instanceof DeskproContainer) {
            $entityManager = $container->getEm();
            $this->searchTermsAliasResolver = SearchTermsAliasResolver::create($entityManager);
        }
    }

    /**
     * @param TicketWebhook  $webhook
     * @param WebhookRequest $request
     *
     * @throws WebhookException
     */
    public function execute(TicketWebhook $webhook, WebhookRequest $request)
    {
        $webhookInvocation = $this->createWebhookInvocation($webhook, $request);

        $searchCriteria = $this->buildTicketSearchCriteria($webhook, $webhookInvocation);
        $maxTickets     = 100;
        $searchCriteria->setLimit($maxTickets + 1);
        $ticketIds = $searchCriteria->getMatches();

        if (count($ticketIds) > $maxTickets) {
            $ticketIds = array_slice($ticketIds, 0, $maxTickets);
        }

        $tickets = [];
        if ($ticketIds) {
            $tickets = $this->ticketManager->getTickets($ticketIds);
        }

        // log difference between matches and tickets
        // perhaps should check again if tickets match the search criteria -> concurrency issues ?

        $triggerTerms = $webhook->getTerms();
        foreach ($tickets as $ticket) {
            try {
                $context = $this->createExecutionContext($webhook, $request, $webhookInvocation);
                if ($triggerTerms->isTriggerMatch($ticket, $context)) {
                    $this->executeWebhook($ticket, $webhook, $context);
                }
            } catch (\Exception $e) {}
        }
    }

    /**
     * @param TicketWebhook $webhook
     * @param WebhookRequest $request
     * @return WebhookInvocation
     * @throws WebhookException
     */
    private function createWebhookInvocation(TicketWebhook $webhook, WebhookRequest $request)
    {
        $converterName = $webhook->getPayloadDecoder();
        $converter     = $this->convertersRegistry->lookupDecoderByName($converterName);
        if (empty($converter)) {
            throw new WebhookException('could not find a suitable decoder');
        }
        $payload = $converter->decode($request);
        return WebhookInvocation::fromRequestAndData($request, $payload);
    }

    private function executeWebhook(Ticket $ticket, TicketWebhook $webhook, ExecutorContextInterface $context)
    {
        $this->ticketManager->markAsManaged($ticket);
        $actions = $webhook->getActions();

        /** @var DeskproContainer $actionContainer */
        $actionContainer = $this->container && $this->container instanceof DeskproContainer ? $this->container : null;
        if ($actionContainer) {
            $actions->setContainer($actionContainer);
        }

        $actions->applyAction($ticket, $context);
        $this->ticketManager->saveTicket($ticket, $context);
    }

    /**
     * @param TicketWebhook  $webhook
     * @param WebhookRequest $request
     * @param WebhookInvocation $payload
     *
     * @return \Application\DeskPRO\Tickets\ExecutorContextInterface
     */
    private function createExecutionContext(TicketWebhook $webhook, WebhookRequest $request, WebhookInvocation $payload)
    {
        $context = $this->ticketManager->createSystemExecutorContext();
        ExecutorContextEnv::setWebhook($context, $webhook);
        ExecutorContextEnv::setWebhookRequest($context, $request);
        ExecutorContextEnv::setWebhookPayload($context, $payload);

        return $context;
    }

    private function buildTicketSearchCriteria(TicketWebhook $webhook, WebhookInvocation $webhookInvocation)
    {
        $terms = $webhook->getSearchTerms();
        $trans       = new LegacyTermsTransformer();
        $termsList = $trans->toLegacyTerms($terms);

        // filter and evaluate search terms
        $searchTerms = array_filter($termsList, function ($term) {
            return $term['op'] != 'ignore';
        });

        $evaluators = [ new TwigScriptEvaluator() ];
        $searchTerms = array_map(function ($term) use ($evaluators, $webhookInvocation) {
            if (SearchTermVars::hasVars($term, $evaluators)) {
                return SearchTermVars::evaluate($term, $evaluators, $webhookInvocation);
            }

            return $term;
        }, $searchTerms);

        if ($this->searchTermsAliasResolver) {
            $searchTerms = $this->searchTermsAliasResolver->resolveAliasTerms($searchTerms);
        }

        $criteria     = new TicketSearch();
        $hasUserTerms = false;
        $hasOrgTerms  = false;
        foreach ($searchTerms as $term) {
            $criteria->addTerm($term['type'], $term['op'], $term['options']);
            $hasUserTerms = !$hasUserTerms && strpos($term['type'], 'person_') === 0;
            $hasOrgTerms  = !$hasOrgTerms && strpos($term['type'], 'org_') === 0;
        }

        if ($hasUserTerms) {
            $criteria->setPersonSearch(new PersonSearch());
        }
        if ($hasOrgTerms) {
            $criteria->setOrganizationSearch(new OrganizationSearch());
        }

        return $criteria;
    }
}
