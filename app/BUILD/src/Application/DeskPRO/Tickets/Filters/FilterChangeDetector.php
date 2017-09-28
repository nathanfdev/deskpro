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

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\People\PermissionChecker\TicketChecker;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Tickets\ExecutorContextInterface;
use DeskPRO\Bundle\AppBundle\Notification\NotificationEventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Class FilterChangeDetector.
 */
class FilterChangeDetector
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var NotificationEventManager
     */
    private $notificationEventManager;

    /**
     * Add a filter check for an agent explicitly. Usually this only goes through
     * detection for chagned filters, but sometimes you need to know if a ticket
     * was in an unaffected filter (e.g., for an 'updated' notification).
     *
     * @var array
     */
    private $explicitFilterIds = [];

    /**
     * @var bool
     */
    private $extended_log_info = false;

    /**
     * @var bool
     */
    private $disable_cache = false;

    /**
     * @var array
     */
    private $cachedAgents = [];

    /**
     * @var LegacyTicketFilter[]
     */
    private $cachedGlobalFilters = null;

    /**
     * @var array
     */
    private $cachedUserFilters = [];

    /**
     * @var TicketSearch[]
     */
    private $cachedSearchers = [];

    /**
     * @var array
     */
    private $cachedPermissions = [];

    /**
     * Constructor.
     *
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     * @param NotificationEventManager $notificationEventManager
     */
    public function __construct(
        EntityManager $em,
        EventDispatcherInterface $eventDispatcher,
        NotificationEventManager $notificationEventManager
    ) {
        $this->em                       = $em;
        $this->connection               = $this->em->getConnection();
        $this->eventDispatcher          = $eventDispatcher;
        $this->notificationEventManager = $notificationEventManager;

        $this->explicitFilterIds = $this->connection->fetchAllCol('
            SELECT DISTINCT filter_id FROM ticket_filter_subscriptions WHERE email_property_change = 1 OR alert_property_change = 1
        ');

        if (isset($GLOBALS['DP_FILTERCHANGEDETECT_DISABLE_CACHE']) && $GLOBALS['DP_FILTERCHANGEDETECT_DISABLE_CACHE']) {
            $this->disable_cache = true;
        }
    }

    /**
     * @param Ticket                   $ticket
     * @param ExecutorContextInterface $context
     * @param array                    $forAgentIds
     *
     * @return FilterChangeSet
     */
    public function getFilterChangeSet(Ticket $ticket, ExecutorContextInterface $context, array $forAgentIds)
    {
        $logger = $context->getLogger();
        $state  = $ticket->getStateChangeRecorder();

        // prepare agents/filters map
        $agents  = $this->getAgents($forAgentIds);
        $filters = $this->getFilters(array_keys($agents));

        $filtersAgentsMap = [];
        $filtersTermMap   = [];

        foreach ($filters as $filterId => $filter) {
            $agentId = $filter['person_id'];

            if ($agentId) {
                $filtersAgentsMap[$filterId][$agentId] = $agents[$agentId];
            } else {
                $filtersAgentsMap[$filterId] = $agents;
            }

            $filtersTermMap[md5(serialize($filter['terms']))][$filterId] = $filter;
        }

        // calc affected filters
        $start   = microtime(true);
        $checker = new AffectedFiltersCheck($ticket, $filtersTermMap, $logger);

        $affectedFilters = $checker->getNewAffectedFilters();
        // add explicit filters
        foreach ($this->explicitFilterIds as $filterId) {
            if (!isset($affectedFilters[$filterId]) && isset($filters[$filterId])) {
                $affectedFilters[$filterId] = $filters[$filterId];
            }
        }

        $logger->info(sprintf('[FilterChangeDetector] Affected filters took %.3fs', microtime(true) - $start));
        $logger->info(sprintf('[FilterChangeDetector] Checking %d filters', count($affectedFilters)));

        $isDepChanged = $state->hasChangedField('department');
        $isNewTicket  = $state->isNewTicket();

        /** @var Ticket $orig_ticket */
        $orig_ticket = $ticket->getOriginalStateClone();
        $new_ticket  = $ticket;

        $scope_counts        = 0;
        $scope_cached_counts = 0;

        /** @var FilterChange[] $changed */
        $changed = [];

        $generic_match_cache  = [];
        $not_cachable_filters = [];

        // Calculate who could actually see it
        $start = microtime(true);

        $ticketVersion  = $state->getStateVersion();
        $distinctAgents = [];
        foreach ($affectedFilters as $filterId => $filter) {
            $distinctAgents += $filtersAgentsMap[$filterId];
        }

        foreach ($distinctAgents as $agentId => $agent) {
            if (isset($this->cachedPermissions[$ticketVersion][$agentId])) {
                // already calculated, skipping
                continue;
            }

            /* @var Person $agent */
            if ($isNewTicket) {
                $see_old = false;
                $see_new = true;
            } else {
                $see_old = true;
                $see_new = true;
            }

            // testing check
            // there is no mock for the PermissionsManager yet
            if (!defined('DP_BOOT_MODE') || DP_BOOT_MODE != 'testing') {
                /** @var TicketChecker $ticketChecker */
                $ticketChecker = $agent->getPermissionsManager()->TicketChecker;
                if ($see_old && !$ticketChecker->canView($orig_ticket)) {
                    $see_old = false;
                }
                if ($see_new && !$ticketChecker->canView($new_ticket)) {
                    $see_new = false;
                }
            }

            $this->cachedPermissions[$ticketVersion][$agentId] = ['old' => $see_old, 'new' => $see_new];
        }

        $logger->debug(sprintf('[FilterChangeDetector] Permissions of %d agents calculated in %.3fs', count($distinctAgents), microtime(true) - $start));

        $time = microtime(true);

        foreach ($affectedFilters as $filterId => $filter) {
            $agentScopes = [];

            foreach ($filtersAgentsMap[$filterId] as $agentId => $agent) {
                $agentPermissions = $this->cachedPermissions[$ticketVersion][$agentId];
                if ($agentPermissions['old'] || $agentPermissions['new']) {
                    $agentScopes[$agentId] = $agent;
                }
            }

            if (!$agentScopes) {
                continue;
            }

            $filter_ts = microtime(true);

            $filter_change      = new FilterChange($filter);
            $changed[$filterId] = $filter_change;

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] ----- BEGIN #%d -- %d scopes -----', $filterId, count($agentScopes)));
            }
            $cached_terms_orig = [];
            $cached_terms_new  = [];

            foreach ($agentScopes as $agentId => $agent) {
                $agentPermOld = $this->cachedPermissions[$ticketVersion][$agentId]['old'];
                $agentPermNew = $this->cachedPermissions[$ticketVersion][$agentId]['new'];

                $orig_match_real = $new_match_real = null;
                $new_match       = $orig_match       = false;

                // RESULT_IS_CACHED
                if (!$this->disable_cache && isset($generic_match_cache[$filterId])) {
                    $pre_orig_match = $generic_match_cache[$filterId]['pre_orig_match'];
                    $pre_new_match  = $generic_match_cache[$filterId]['pre_new_match'];
                    $orig_match     = $generic_match_cache[$filterId]['orig_match'];
                    $new_match      = $generic_match_cache[$filterId]['new_match'];

                    if (!$agentPermOld) {
                        $orig_match = false;
                    }
                    if (!$agentPermNew) {
                        $new_match = false;
                    }

                    if ($pre_orig_match && $agentPermOld) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($pre_new_match && $agentPermNew) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    ++$scope_cached_counts;

                    // RESULT_NOT_CACHED
                } else {
                    $reset_status = false;
                    if ($filter['sys_name']) {
                        // System filters are special in that we ignore status/hold
                        // for notifications
                        $searcher = $this->getOrCreateSearcher($filter, [
                            ['type' => 'status', 'op' => 'ignore'],
                            ['type' => 'hidden_status', 'op' => 'ignore'],
                            ['type' => 'is_hold', 'op' => 'ignore'],
                        ]);

                        // Reset because we have to re-run to get proper result for add/del lists
                        $reset_status = true;
                    } else {
                        $searcher = $this->getOrCreateSearcher($filter);
                    }

                    $searcher->setPersonContext($agent);

                    $orig_match_failterm = null;
                    $new_match_failterm  = null;

                    if ($isDepChanged) {
                        if (!$isNewTicket && !$agentPermOld) {
                            $orig_match          = false;
                            $orig_match_failterm = 'ticket.department_id';
                        }

                        if (!$agentPermNew) {
                            $new_match          = false;
                            $new_match_failterm = 'ticket.department_id';
                        }
                    }

                    if ($orig_match_failterm === null) {
                        if ($isNewTicket) {
                            // there is no such thing as an original match with a new ticket
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm, $cached_terms_orig);
                            if ($this->extended_log_info) {
                                $logger->debug('[FilterChangeDetector] performed orig_ticket check -- '.($orig_match ? 'yes' : 'no'));
                            }
                        }
                        $orig_match_real = $orig_match;
                    }

                    if ($new_match_failterm === null) {
                        $new_match      = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm, $cached_terms_new);
                        $new_match_real = $new_match;

                        if ($this->extended_log_info) {
                            $logger->debug('[FilterChangeDetector] performed new_ticket check -- '.($new_match ? 'yes' : 'no'));
                        }
                    }

                    if ($orig_match && $agentPermOld) {
                        $filter_change->originalMatchForAgent($agent);
                    }
                    if ($new_match && $agentPermNew) {
                        $filter_change->newMatchForAgent($agent);
                    }

                    $pre_orig_match = $orig_match;
                    $pre_new_match  = $new_match;

                    if ($reset_status) {
                        $searcher = $this->getOrCreateSearcher($filter);
                        $searcher->setPersonContext($agent);

                        if ($isNewTicket) {
                            $orig_match = false;
                        } else {
                            $orig_match = $searcher->doesTicketMatch($orig_ticket, 'orig_match', $orig_match_failterm, $cached_terms_orig);
                            if ($this->extended_log_info) {
                                $logger->debug('[FilterChangeDetector] performed orig_ticket check (reset status) -- '.($orig_match ? 'yes' : 'no'));
                            }
                        }

                        $new_match = $searcher->doesTicketMatch($new_ticket, null, $new_match_failterm, $cached_terms_new);
                        if ($this->extended_log_info) {
                            $logger->debug('[FilterChangeDetector] performed new_ticket check (reset status) -- '.($new_match ? 'yes' : 'no'));
                        }

                        $orig_match_real = $orig_match;
                        $new_match_real  = $new_match;
                    }

                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] New match: %s -- Orig match: %s', $new_match ? 'yes' : 'no', $orig_match ? 'yes' : 'no'));
                    }
                    if (!$orig_match) {
                        if ($this->extended_log_info) {
                            $logger->debug(sprintf("[FilterChangeDetector] \tOrig failed term: %s", $orig_match_failterm));
                        }
                    }
                    if (!$new_match) {
                        if ($this->extended_log_info) {
                            $logger->debug(sprintf("[FilterChangeDetector] \tNew failed term: %s", $new_match_failterm));
                        }
                    }

                    if ($new_match_real !== null && $orig_match_real !== null && !isset($generic_match_cache[$filterId]) && !isset($not_cachable_filters[$filterId])) {
                        if (!$searcher->needsPersonContext()) {

                            // Two types of matches:

                            // Pre-matches are matches with any special logic
                            // applied to ignore status.
                            // This is required when NEW tickets are created
                            // so we know if a ticket sholud fire events
                            // for a particular filter.
                            // Ex: A new ticket created as RESOLVED wont technically
                            // ever be in 'All tickets' because that fitler has a criteria
                            // for status=awaiting_agent.
                            // But we still want notifications to send for new tickets,
                            // so we have to ignore that status criteria.

                            // Non-pre matches are "real" matches. This is how we determine
                            // where the ticket is *right now*.

                            $generic_match_cache[$filterId] = [
                                'pre_orig_match' => $pre_orig_match,
                                'pre_new_match'  => $pre_new_match,
                                'orig_match'     => $orig_match,
                                'new_match'      => $new_match,
                            ];
                        } else {
                            $not_cachable_filters[$filterId] = $filterId;
                        }
                    }
                } // end RESULT_NOT_CACHED

                if (!$orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both no-match)', $agentId));
                    }
                } elseif ($orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: nochange (both match)', $agentId));
                    }
                } elseif ($orig_match and !$new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: removed from list', $agentId));
                    }
                    $filter_change->removeForAgent($agent);
                } elseif (!$orig_match and $new_match) {
                    if ($this->extended_log_info) {
                        $logger->debug(sprintf('[FilterChangeDetector] Agent scope %d: added to list', $agentId));
                    }
                    $filter_change->addForAgent($agent);
                }

                ++$scope_counts;
            }

            if ($this->extended_log_info) {
                $logger->debug(sprintf('[FilterChangeDetector] DONE FILTER #%d :: %.4fs', $filterId, microtime(true) - $filter_ts));
            }
        }

        $changed_filters = [];
        foreach ($changed as $fid => $filter_change) {
            if ($filter_change->hasOriginalMatches() || $filter_change->hasNewMatches()) {
                $changed_filters[$fid] = $filter_change;
            }
        }

        $logger->debug(sprintf('[FilterChangeDetector] The following filters could not be optimised: %s', implode(', ', $not_cachable_filters)));
        $logger->info(sprintf('[FilterChangeDetector] Found %d filters in %d iterations (%d of those were cached). Time: %.4fs', count($changed_filters), $scope_counts, $scope_cached_counts, microtime(true) - $time));

        $set = new FilterChangeSet(
            $ticket,
            $state->getStateVersion(),
            $checker->getAffectedFilters(),
            $changed_filters,
            $checker->getNewestFieldVersions(),
            $this->eventDispatcher,
            $this->notificationEventManager
        );

        if ($context) {
            $context->getVars()->set('filter_change_set', $set);
        }

        foreach ($set->getChangedFilters() as $change) {
            $addedAgentIds   = array_keys($change->getAgentsAdded());
            $removedAgentIds = array_keys($change->getAgentsRemoved());

            if ($addedAgentIds || $removedAgentIds) {
                $logger->info(sprintf(
                    '[FilterChangeDetector] Summary: Filter %d -- AddedAgents(%s) -- RemovedAgents(%s)',
                    $change->getFilter()['id'],
                    implode(', ', $addedAgentIds ?: ['none']),
                    implode(', ', $removedAgentIds ?: ['none'])
                ));
            }
        }

        return $set;
    }

    /**
     * @param array $agentIds
     *
     * @return Person[]
     */
    private function getAgents(array $agentIds)
    {
        if (!$agentIds) {
            return [];
        }

        // load from cache
        $agents   = array_intersect_key($this->cachedAgents, array_combine($agentIds, $agentIds));
        $agentIds = array_diff($agentIds, array_keys($agents));

        // load from db
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('p')
            ->from(Person::class, 'p')
            ->where(
                'p.is_agent = 1',
                'p.is_disabled = 0',
                'p.is_deleted = 0',
                'p.id IN (:ids)'
            )
            ->setParameter('ids', $agentIds);

        $query = $qb->getQuery();
        $query->setFetchMode(Person::class, 'primary_email', ClassMetadata::FETCH_LAZY);

        /** @var Person[] $result */
        $result = $query->getResult();
        foreach ($result as $agent) {
            $agents[$agent->getId()] = $agent;
        }

        $this->cachedAgents += $agents;

        return $agents;
    }

    /**
     * @param array $agentIds
     *
     * @return array
     */
    private function getFilters(array $agentIds)
    {
        if (!$agentIds) {
            return [];
        }

        $filters = [];

        // load global filters
        if (null === $this->cachedGlobalFilters) {
            $this->cachedGlobalFilters = [];

            $qb = $this->em->getConnection()->createQueryBuilder();
            $qb
                ->select('f.id, f.sys_name, f.person_id, f.terms')
                ->from('ticket_filters', 'f')
                ->where('f.person_id IS NULL');

            $result = $qb->execute()->fetchAll();
            foreach ($result as $filter) {
                $filter['terms'] = json_decode($filter['terms'], true);

                $this->cachedGlobalFilters[$filter['id']] = $filter;
            }
        }

        $filters += $this->cachedGlobalFilters;

        // load user filters
        foreach ($agentIds as $agentId) {
            if (isset($this->cachedUserFilters[$agentId])) {
                $filters += $this->cachedUserFilters[$agentId];
            }
        }

        $agentIds = array_diff($agentIds, array_keys($this->cachedUserFilters));

        $qb = $this->em->getConnection()->createQueryBuilder();
        $qb
            ->select('f.id, f.sys_name, f.person_id, f.terms')
            ->from('ticket_filters', 'f')
            ->where('f.person_id IN (:ids)')
            ->setParameter('ids', $agentIds, Connection::PARAM_INT_ARRAY);

        $result = $qb->execute()->fetchAll();
        foreach ($result as $filter) {
            $filter['terms'] = json_decode($filter['terms'], true);

            $filters[$filter['id']]                                       = $filter;
            $this->cachedUserFilters[$filter['person_id']][$filter['id']] = $filter;
        }

        return $filters;
    }

    /**
     * @param array $filter
     * @param array $forceTerms
     *
     * @return TicketSearch
     */
    public function getOrCreateSearcher(array $filter, array $forceTerms = [])
    {
        return LegacyTicketFilter::createSearcher($filter['sys_name'], $filter['terms'], $forceTerms);

        /* todo -- is there some kind of state being saved in the object making it unsuitable for caching?
        $key = md5($filter['sys_name'].serialize(array_merge($filter['terms'], $forceTerms)));
        if (!isset($this->cachedSearchers[$key])) {
            $this->cachedSearchers[$key] = LegacyTicketFilter::createSearcher($filter['sys_name'], $filter['terms'], $forceTerms);
        }

        return $this->cachedSearchers[$key];
        */
    }
}
