<?php

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\TicketResults;
use Application\AgentBundle\Controller\JsonRenderer\TicketListRenderer;
use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\PeopleFields;
use Application\DeskPRO\CustomFields\TicketFields;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\LabelDef;
use Application\DeskPRO\Entity\LegacyTicketFilter;
use Application\DeskPRO\Entity\Organization;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonPref;
use Application\DeskPRO\Entity\Problem;
use Application\DeskPRO\Entity\ResultCache;
use Application\DeskPRO\Entity\Sla;
use Application\DeskPRO\Entity\TextSnippet;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketFlagged;
use Application\DeskPRO\Entity\TicketMacro;
use Application\DeskPRO\Entity\TicketSla;
use Application\DeskPRO\Searcher\TicketSearch;
use Application\DeskPRO\Tickets\GroupingCounter;
use Application\DeskPRO\Tickets\TicketActions\ActionsCollection;
use Application\DeskPRO\Tickets\TicketActions\ActionsFactory;
use Application\DeskPRO\Tickets\TicketResultsDisplay;
use Application\DeskPRO\Tickets\Tickets;
use Application\DeskPRO\UI\RuleBuilder;
use DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation;
use DeskPRO\Bundle\AppBundle\Entity\SnippetUseLog;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\TicketFilters\Context;
use DeskPRO\Bundle\AppBundle\TicketFilters\TicketSearchParams;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;
use DeskPRO\Component\Util\ListUtils;
use DeskPRO\Component\Util\RegexUtils;
use DpSys\LowError\SystemErrorHandler;
use Orb\Util\Arrays;
use Orb\Util\Numbers;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles ticket searches.
 */
class TicketSearchController extends AbstractController
{
    public function getSectionDataAction()
    {
        $data = [];

        //------------------------------
        // Filters
        //------------------------------

        $filter_info      = App::getApi('tickets.filters')->getGroupedFiltersForPerson($this->person);
        $all_filters      = $filter_info['all_filters'];
        $sys_filters      = $filter_info['sys_filters'];
        $sys_filters_hold = $filter_info['sys_filters_hold'];
        $archive_filters  = $filter_info['archive_filters'];
        $custom_filters   = $filter_info['custom_filters'];

        $filter_id_matches = App::getApi('tickets.filters')->getAllIdsForFiltersCollection(array_merge($sys_filters, $sys_filters_hold), $this->person);
        $filter_id_matches = Arrays::castToTypeDeep($filter_id_matches, 'int', 'int');

        $problem_filters = [];
        foreach ($all_filters as $filter) {
            if (Entity\Problem::FILTER_PREFIX === substr($filter->sys_name, 0, 8)) {
                $problem_filters[substr($filter->sys_name, 8)] = $filter;
            }
        }

        $archive_filter_counts = App::getApi('tickets.filters')->getAllCountsForFiltersCollection($archive_filters, $this->person);
        $problem_filter_counts = App::getApi('tickets.filters')->getAllCountsForFiltersCollection(
            $problem_filters,
            $this->person
        );

        //agent.ui.filter
        $filter_show_options = $this->db->fetchAllKeyValue("
            SELECT name, value_str
            FROM people_prefs
            WHERE person_id = ? AND (name LIKE 'agent.ui.filter-visibility.%' OR name LIKE 'agent.ui.sla.filter-visibility.%')
        ", [$this->person->id]);

        /*
         * @var Problem $rep
         */
        $open_problems   = [];
        $closed_problems = [];
        if ($this->settings->get('core.problems.enabled') && $this->person->hasPerm('agent_problems.view')) {
            $rep      = $this->em->getRepository(Problem::class);
            $problems = $rep->findBy([], ['title' => 'asc']);

            foreach ($problems as $problem) {
                $problem->is_open
                    ? $open_problems[]   = $problem->toApiData()
                    : $closed_problems[] = $problem->toApiData();
            }
        }

        //------------------------------
        // SLAs
        //------------------------------

        $sla_filter = $this->person->getPref('agent.ui.sla.ticket-filter', 'all');
        $slas       = $this->em->getRepository(Sla::class)->getAllSlas();
        $sla_counts = $this->em->getRepository(TicketSla::class)->getCachedTicketSlaCountsForAgentInterface($slas, $sla_filter, $this->person);

        //------------------------------
        // Misc
        //------------------------------

        $flags       = ['blue', 'green', 'orange', 'pink', 'purple', 'red', 'yellow'];
        $flag_counts = $this->em->getRepository(TicketFlagged::class)->getCountsForPerson($this->person);

        $label_lister = new \Application\DeskPRO\Labels\LabelLister('tickets');
        $index        = $label_lister->getIndexList();

        $label_counts = $this->em->getRepository(LabelDef::class)->getLabelCounts('ticket', 25);
        $cloud_gen    = new \Application\DeskPRO\UI\TagCloud($label_counts);
        $cloud        = $cloud_gen->getCloud();

        $initial_inbox_grouping = $this->em->getRepository(PersonPref::class)->getPrefgroupForPersonId('agent.ui.ticket-source-grouping', $this->person->id);

        $term_options = App::getApi('tickets')->getTicketOptions($this->person);

        $ticket_field_defs                    = App::getApi('custom_fields.tickets')->getEnabledFields();
        $custom_fields                        = App::getApi('custom_fields.tickets')->getFieldsDisplayArray($ticket_field_defs);
        $term_options['custom_ticket_fields'] = $custom_fields;

        $brands = $this->em->getRepository(Brand::class)->findAll();

        $data['section_html'] = $this->renderView('AgentBundle:TicketSearch:window-section.html.twig', [
            'brands'                 => $brands,
            'sys_filters'            => $sys_filters,
            'sys_filters_hold'       => $sys_filters_hold,
            'archive_filters'        => $archive_filters,
            'problem_filters'        => $problem_filters,
            'archive_filter_counts'  => $archive_filter_counts,
            'problem_filter_counts'  => $problem_filter_counts,
            'filter_id_matches'      => $filter_id_matches,
            'custom_filters'         => $custom_filters,
            'flags'                  => $flags,
            'flag_counts'            => $flag_counts,
            'filter_show_options'    => $filter_show_options,
            'labels_index'           => $index,
            'labels_cloud'           => $cloud,
            'initial_inbox_grouping' => $initial_inbox_grouping,

            'open_problems'   => $open_problems,
            'closed_problems' => $closed_problems,

            'slas'       => $slas,
            'sla_counts' => $sla_counts,
            'sla_filter' => $sla_filter,

            'term_options' => $term_options,
        ]);

        $data['filter_id_matches'] = $filter_id_matches;

        return $this->createJsonResponse($data);
    }

    public function reloadArchiveSectionAction()
    {
        $archive_counts = $this->em->getRepository(Ticket::class)->getArchiveCounts();

        return $this->render('AgentBundle:TicketSearch:window-section-archive.html.twig', [
            'archive_counts' => $archive_counts,
        ]);
    }

    public function refreshSectionDataAction($section)
    {
        switch ($section) {
            case 'labels':
                return $this->getLabelsSectionAction();

            case 'flagged':
                return $this->getFlaggedSectionAction();

            default:
                return $this->createResponse('');
        }
    }

    public function getLabelsSectionAction()
    {
        $label_lister = new \Application\DeskPRO\Labels\LabelLister('tickets');
        $index        = $label_lister->getIndexList();

        $label_counts = $this->em->getRepository(LabelDef::class)->getLabelCounts('ticket', 25);
        $cloud_gen    = new \Application\DeskPRO\UI\TagCloud($label_counts);
        $cloud        = $cloud_gen->getCloud();

        return $this->render('AgentBundle:TicketSearch:pane-labels-index.html.twig', [
            'labels_index' => $index,
            'labels_cloud' => $cloud,
        ]);
    }

    public function getFilterCountsAction()
    {
        $all_counts = App::getApi('tickets.filters')->getAllCountsCustomFilters($this->person);

        return $this->createJsonResponse($all_counts);
    }

    public function getSlaCountsAction()
    {
        $sla_filter = $this->person->getPref('agent.ui.sla.ticket-filter', 'all');
        $slas       = $this->em->getRepository(Sla::class)->getAllSlas();
        $sla_counts = $this->em->getRepository(TicketSla::class)->getCachedTicketSlaCountsForAgentInterface($slas, $sla_filter, $this->person);

        return $this->createJsonResponse([
            'counts'     => $sla_counts,
            'sla_filter' => $sla_filter,
        ]);
    }

    public function getFlaggedSectionAction()
    {
        $flags       = ['blue', 'green', 'orange', 'pink', 'purple', 'red', 'yellow'];
        $flag_counts = $this->em->getRepository(TicketFlagged::class)->getCountsForPerson($this->person);

        return $this->render('AgentBundle:TicketSearch:window-flagged.html.twig', [
            'flags'       => $flags,
            'flag_counts' => $flag_counts,
        ]);
    }

    public function quickSearchAction()
    {
        $limit = $this->in->getUInt('limit');
        if (!$limit) {
            $limit = 10;
        }
        $limit = min($limit, 100);

        $q = $this->in->getString('q');
        if (!$q) {
            $q = $this->in->getString('term');
        }

        if (!$q && !$this->in->getUInt('person_id')) {
            return $this->createJsonResponse([]);
        }

        if ($this->container->getSetting('elastica.enabled') && !$this->in->getUInt('person_id')) {
            try {
                $elasticsearch = $this->container->get('deskpro.search_manager.elasticsearch');
                $elasticsearch->setPersonContext($this->person);

                list($results, $result_meta, $people_top) = $elasticsearch->quickSearch($q, 'date_active', ['ticket']);

                if (isset($results['ticket'])) {
                    $results = $results['ticket'];
                } else {
                    $results = [];
                }

                $results = array_slice($results, 0, $limit);

                $output = [];
                foreach ($results as $ticket) {
                    $output[] = [
                        'id'            => $ticket->id,
                        'value'         => $ticket->id,
                        'subject'       => $ticket->subject,
                        'status'        => $ticket->getStatusCode(),
                        'last_activity' => $ticket->getLastActivityDate()->getTimestamp(),
                    ];
                }
            } catch (\Exception $e) {
                SystemErrorHandler::logException($e);
                $searcher = new TicketSearch();
                $searcher->setPerson($this->person);
                $searcher->setOrderBy('ticket.date_created');

                if ($person_id = $this->in->getUInt('person_id')) {
                    $searcher->addTerm('person', 'is', ['person_id' => $person_id]);
                    $searcher->setLimit($this->in->getUInt('limit') ?: 10);
                    $results = $searcher->getMatches();
                    $results = Arrays::castToType($results, 'integer');
                } else {
                    $searcher->addTerm('ticket_message', 'is', ['query' => $q]);
                    $searcher->addTerm('date_created', 'gte', ['date1' => strtotime('-60 days')]);
                    $results = $searcher->getMatches();
                    $results = Arrays::castToType($results, 'integer');

                    if (ctype_digit($q) || preg_match('/#^([0-9]+)$/', $q)) {
                        if ($q[0] == '#') {
                            $q = substr($q, 1);
                        }
                        array_unshift($results, $q);
                    }
                }

                $results = array_slice($results, 0, $limit);

                $output = [];
                foreach (App::getEntityRepository(Ticket::class)->getByIds($results, true) as $ticket) {
                    $output[] = [
                        'id'            => $ticket->id,
                        'value'         => $ticket->id,
                        'subject'       => $ticket->subject,
                        'status'        => $ticket->getStatusCode(),
                        'last_activity' => $ticket->getLastActivityDate()->getTimestamp(),
                    ];
                }
            }
        } else {
            $searcher = new TicketSearch();
            $searcher->setPerson($this->person);
            $searcher->setOrderBy('ticket.date_created');

            if ($person_id = $this->in->getUInt('person_id')) {
                $searcher->addTerm('person', 'is', ['person_id' => $person_id]);
                $searcher->setLimit($this->in->getUInt('limit') ?: 10);
                $results = $searcher->getMatches();
                $results = Arrays::castToType($results, 'integer');
            } else {
                $searcher->addTerm('ticket_message', 'is', ['query' => $q]);
                $searcher->addTerm('date_created', 'gte', ['date1' => strtotime('-60 days')]);
                $results = $searcher->getMatches();
                $results = Arrays::castToType($results, 'integer');

                if (ctype_digit($q) || preg_match('/#^([0-9]+)$/', $q)) {
                    if ($q[0] == '#') {
                        $q = substr($q, 1);
                    }
                    array_unshift($results, $q);
                }
            }

            $results = array_slice($results, 0, $limit);

            $output = [];
            foreach (App::getEntityRepository(Ticket::class)->getByIds($results, true) as $ticket) {
                $output[] = [
                    'id'            => $ticket->id,
                    'value'         => $ticket->id,
                    'subject'       => $ticket->subject,
                    'status'        => $ticket->getStatusCode(),
                    'last_activity' => $ticket->getLastActivityDate()->getTimestamp(),
                ];
            }
        }

        return $this->createJsonResponse($output);
    }

    /**
     * Render a new pageset.
     *
     * The client has a full list of IDs from a search. When he wants the next page,
     * he sends a set of new IDs in the result set and we return the HTML to inject
     * into his view.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     */
    public function getTicketPageAction()
    {
        $ticket_ids = $this->in->getCleanValueArray('result_ids', 'uint', 'discard');
        $ticket_ids = Arrays::removeFalsey($ticket_ids);
        $ticket_ids = array_unique($ticket_ids);

        $tickets = $this->em->getRepository(Ticket::class)->getTicketsResultsFromIds($ticket_ids);
        $tickets = Arrays::orderIdArray($ticket_ids, $tickets);

        $display_fields = $this->in->getCleanValueArray('display_fields', 'string', 'discard');
        if (!$display_fields) {
            $display_fields = ['department', 'agent', 'agent_team'];
        }

        $has_t_fields = false;
        $has_u_fields = false;

        foreach ($display_fields as $f) {
            if (strpos($f, 'ticket_fields[') === 0) {
                $has_t_fields = true;
            }
            if (strpos($f, 'person_fields[') === 0) {
                $has_u_fields = true;
            }
        }

        $all_custom_fields      = [];
        $user_all_custom_fields = [];

        if ($has_t_fields || $has_u_fields) {
            $field_manager      = $this->container->getTicketFieldManager();
            $user_field_manager = $this->container->getPersonFieldManager();

            foreach ($tickets as $t) {
                if ($has_t_fields) {
                    $all_custom_fields[$t->id] = $field_manager->getDisplayArrayForObject($t);
                }

                if ($has_u_fields) {
                    $p                              = $t->person;
                    $user_all_custom_fields[$p->id] = $user_field_manager->getDisplayArrayForObject($p);
                }
            }
        }

        // Accept changes to apply for previewing
        // - We just apply the changes but dont save them, they'll be
        //   properly displayed in the listing.
        $collection     = null;
        $changed_fields = [];

        if ($macro_id = $this->in->getUInt('run_macro_id')) {
            $macro      = $this->em->find(TicketMacro::class, $macro_id);
            $actions    = null;
            $collection = $macro->getActionsCollection();

            foreach ($collection->getActions() as $action) {
                if ($action instanceof \Application\DeskPRO\Tickets\TicketActions\ActionInterface) {
                    $action->setMetaData(['is_preview' => true]);
                }
            }
        } else {
            $actions = $this->in->getCleanValueArray('actions', 'raw', 'string');
        }

        if (($actions || $collection) && $tickets) {
            if (!$collection) {
                $factory    = new ActionsFactory();
                $collection = new ActionsCollection();
                foreach ($actions as $name => $opt) {
                    $action = $factory->createFromForm($name, $opt);
                    if ($action) {
                        if ($action instanceof \Application\DeskPRO\Tickets\TicketActions\ActionInterface) {
                            $action->setMetaData(['is_preview' => true]);
                        }

                        $collection->add($action);

                        $display_fields[] = $name;
                    }
                }
            }

            foreach ($tickets as $t) {
                $ticket_changes = $collection->getApplyActions($t, $this->person);
                $collection->apply(null, $t, $this->person);

                if ($ticket_changes) {
                    $ticket_changed_fields = [];
                    foreach ($ticket_changes as $change) {
                        $ticket_changed_fields[$change['action']] = true;
                        $changed_fields[$t['id']]                 = $ticket_changed_fields;
                    }
                }
            }

            $display_fields = array_unique($display_fields);
        }

        $ticket_field_defs = App::getApi('custom_fields.tickets')->getEnabledFields();
        $person_field_defs = App::getApi('custom_fields.people')->getEnabledFields();

        $ticket_display = new TicketResultsDisplay($tickets);
        $ticket_display->setPersonContext($this->person);

        $tpl = 'part-results-simple-ext.html.twig';
        if ($this->in->getString('view_type') == 'list') {
            $tpl = 'part-results-list.html.twig';
        }

        return $this->render("AgentBundle:TicketSearch:$tpl", [
            'ticket_display'    => $ticket_display,
            'tickets'           => $tickets,
            'display_fields'    => $this->normalizeDisplayFields($display_fields),
            'ticket_field_defs' => $ticket_field_defs,
            'person_field_defs' => $person_field_defs,
            'changed_fields'    => $changed_fields,
            'all_custom_fields' => $all_custom_fields,
        ]);
    }

    /**
     * We get in an array of ticket ID batches. Each batch is identified by some ID,
     * usually a filter ID from Tickets.js section.
     *
     * array(
     *     'batchId' => array('grouping' => 'xxx', 'ticket_ids' => array(x,x,x))
     * )
     *
     * We group the batches, and return titles, search URL's, and counts for the batch
     * using the same batch ID:
     *
     * array(
     *     'batchId' => 'subgroup_html'
     * )
     */
    public function groupTicketsAction()
    {
        $ticket_batches = $this->in->getArrayValue('batches');
        $batches        = [];

        $save_pref = $this->in->getBool('save_pref');
        $prefs     = [];

        foreach ($ticket_batches as $batch_id => $ticket_batch) {
            if ($save_pref) {
                // note $batch_id is a filter ID
                $prefs['agent.ui.ticket-source-grouping.'.$batch_id] = $ticket_batch['grouping'];
            }

            if ($ticket_batch && !empty($ticket_batch['ticket_ids']) && is_string($ticket_batch['ticket_ids'])) {
                $ticket_batch['ticket_ids'] = explode(',', $ticket_batch['ticket_ids']);
                $ticket_batch['ticket_ids'] = Arrays::func($ticket_batch['ticket_ids'], 'trim');
                $ticket_batch['ticket_ids'] = Arrays::castToType($ticket_batch['ticket_ids'], 'int', 'discard');
                $ticket_batch['ticket_ids'] = Arrays::removeFalsey($ticket_batch['ticket_ids']);
            }

            if (!$ticket_batch || empty($ticket_batch['ticket_ids'])) {
                $batches[$batch_id] = '';
                continue;
            }

            $grouper = new GroupingCounter();
            $grouper->setGrouping($ticket_batch['grouping']);
            $grouper->setMode('specify', $ticket_batch['ticket_ids']);

            $grouped_info = $grouper->getDisplayArray();

            if ($ticket_batch['grouping'] == 'urgency') {
                $grouped_info['group1_structure'] = array_reverse($grouped_info['group1_structure']);
            }

            $batches[$batch_id] = $this->renderView('AgentBundle:TicketSearch:window-filter-groupresult.html.twig', [
                'grouping_var' => $ticket_batch['grouping'],
                'grouped_info' => $grouped_info,
            ]);
        }

        if ($prefs) {
            $this->em->getConnection()->beginTransaction();

            try {
                foreach ($prefs as $k => $v) {
                    $p = $this->person->setPreference($k, $v);
                    $this->em->persist($p);
                }

                $this->em->flush();
                $this->em->getConnection()->commit();
            } catch (\Exception $e) {
                $this->em->getConnection()->rollback();
                throw $e;
            }
        }

        return $this->createJsonResponse($batches);
    }

    public function getFlaggedSectionDataAction()
    {
        $data                = [];
        $data['flag_counts'] = $this->em->getRepository(TicketFlagged::class)->getCountsForPerson($this->person);

        return $this->createJsonResponse($data);
    }

    public function getSubgroupCountsAction()
    {
        if ($filter_id = $this->in->getUInt('filter_id')) {
            /** @var $filter \Application\DeskPRO\Entity\LegacyTicketFilter */
            $filter = $this->em->getRepository(LegacyTicketFilter::class)->find($filter_id);

            if (!$filter) {
                throw $this->createNotFoundException();
            }

            $searcher = $filter->getSearcher();
            $searcher->setPerson($this->person);

            $set_group_term   = null;
            $set_group_option = null;
            if ($this->in->getString('set_group_term')) {
                $set_group_term   = $this->in->getString('set_group_term');
                $set_group_option = $this->in->getString('set_group_option');

                $term = GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
                if ($term) {
                    $type   = $term['type'];
                    $op     = $term['op'];
                    $choice = $term;
                    unset($choice['type'], $choice['op']);

                    $searcher->addTerm($type, $op, $choice);
                }
            }

            $results = $searcher->getMatches();

            $group_by = $this->person->getPref('agent.ui.ticket-filter-group-by.'.$filter['id']);
            if ($this->in->checkIsset('group_by')) {
                $group_by = $this->in->getString('group_by');
            } elseif ($filter['group_by']) {
                $group_by = $filter['group_by'];
            }
        } elseif ($sla_id = $this->in->getUInt('sla_id')) {
            /** @var $sla \Application\DeskPRO\Entity\Sla */
            $sla = $this->em->getRepository(Sla::class)->find($sla_id);

            if (!$sla) {
                throw $this->createNotFoundException();
            }

            $searcher = new TicketSearch();
            $searcher->setPerson($this->person);

            $sla_filter = $this->person->getPref('agent.ui.sla.ticket-filter', 'all');
            if ($sla_filter == 'agent') {
                $searcher->addTerm(TicketSearch::TERM_AGENT, 'is', $this->person->id);
            } elseif ($sla_filter == 'team') {
                $searcher->addTerm(TicketSearch::TERM_AGENT_TEAM, 'is', $this->person->getAgentTeamIds());
            }

            $searcher->addTerm(TicketSearch::TERM_SLA_COMPLETED, 'is', [
                'is_completed' => 0,
                'sla_id'       => $sla_id,
            ]);

            if ($sla_status = $this->in->getString('sla_status')) {
                $searcher->addTerm(TicketSearch::TERM_SLA_STATUS, 'is', [
                    'sla_status' => $sla_status,
                    'sla_id'     => $sla_id,
                ]);
            }

            if ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_WAITING_TIME) {
                $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
            } elseif ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_FIRST_RESPONSE) {
                $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
            } else {
                $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', ['awaiting_agent', 'awaiting_user']);
            }

            $set_group_term   = null;
            $set_group_option = null;
            if ($this->in->getString('set_group_term')) {
                $set_group_term   = $this->in->getString('set_group_term');
                $set_group_option = $this->in->getString('set_group_option');

                $term = GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
                if ($term) {
                    $type   = $term['type'];
                    $op     = $term['op'];
                    $choice = $term;
                    unset($choice['type'], $choice['op']);

                    $searcher->addTerm($type, $op, $choice);
                }
            }

            $results = $searcher->getMatches();

            $group_by = $this->person->getPref('agent.ui.ticket-sla-group-by.'.$sla['id']);
            if ($this->in->getString('group_by')) {
                $group_by = $this->in->getString('group_by');

                App::getEntityRepository(PersonPref::class)->savePref(
                    $this->person,
                    'agent.ui.ticket-sla-group-by.'.$sla['id'],
                    $group_by
                );
            }
        } else {
            return $this->createJsonResponse(['error' => 'invalid type']);
        }

        $helper = new Helper\TicketResults($this);
        $helper->setTicketIds($results);

        $helper->setGroupField($group_by);

        return $this->createJsonResponse(['group_display' => $helper->getGroupDisplayInfo()]);
    }

    public function runCustomFilterAction()
    {
        $result_cache = false;
        if ($this->in->getUInt('cache_id')) {
            $result_cache = $this->em->getRepository(ResultCache::class)->find($this->in->getUInt('cache_id'));
            if ($result_cache['person_id'] != $this->person['id']) {
                $result_cache = false;
            }
        }

        $do_run = false;

        $terms    = [];
        $order_by = $this->person->getPref('agent.ui.ticket-basic-order-by.general');
        $group_by = $this->in->getString('group_by');
        $searcher = null;

        //------------------------------
        // If there's no result set, we're running it for the first time
        //------------------------------

        if (!$result_cache) {
            $term_rules = RuleBuilder::newTermsBuilder();
            $terms      = $term_rules->readForm($this->in->getCleanValueArray('terms', 'raw', 'discard'));

            $set_terms_map = [
                'department'   => ['op' => 'contains', 'options' => []],
                'status'       => ['op' => 'contains', 'options' => []],
                'agent'        => ['op' => 'contains', 'options' => []],
                'agent_team'   => ['op' => 'contains', 'options' => []],
                'participant'  => ['op' => 'contains', 'options' => []],
                'category'     => ['op' => 'contains', 'options' => []],
                'product'      => ['op' => 'contains', 'options' => []],
                'priority'     => ['op' => 'contains', 'options' => []],
                'workflow'     => ['op' => 'contains', 'options' => []],
                'organization' => ['op' => 'contains', 'options' => []],
                'language'     => ['op' => 'contains', 'options' => []],
                'sla'          => ['op' => 'contains', 'options' => []],
            ];

            foreach ($set_terms_map as $name => $info) {
                $in_val = $this->container->getIn()->getCleanValueArray('set_term.'.$name, 'raw', 'discard');
                if ($in_val) {
                    $new_term            = $info;
                    $new_term['options'] = $in_val;
                    Arrays::unshiftAssoc($new_term, 'type', $name);
                    $terms[] = $new_term;
                }
            }

            foreach ($this->container->getSystemService('ticket_fields_manager')->getFields() as $field) {
                $in_val = $this->container->getIn()->getValue('set_term.field_'.$field->getId());
                if ($in_val) {
                    $terms[] = ['type' => 'ticket_field['.$field->getId().']', 'op' => 'is', 'options' => ['value' => $in_val]];
                }
            }

            $typesWithAllowedEmptyOptions = ['feedback_links'];
            foreach ($this->in->getCleanValueArray('terms_expanded', 'raw', 'raw') as $type => $info) {
                if (
                    (!in_array($type, $typesWithAllowedEmptyOptions) && empty($info['options']))
                    || empty($info['op'])) {
                    continue;
                }

                $opts = $info['options'];

                foreach ($opts as &$_v) {
                    if (is_array($_v)) {
                        $_v = Arrays::func($_v, 'trim');
                        $_v = Arrays::removeEmptyArray($_v);
                    } elseif (trim($_v) === '') {
                        $_v = null;
                    }
                }
                unset($_v);

                $opts = Arrays::removeValue($opts, null, true);
                $opts = Arrays::removeValue($opts, false, true);

                if (!$opts && !in_array($type, $typesWithAllowedEmptyOptions)) {
                    continue;
                }

                $terms[] = ['type' => $type, 'op' => $info['op'], 'options' => $opts];
            }

            if ($this->in->getString('query')) {
                $terms[] = ['type' => 'text', 'op' => 'is', 'options' => ['query' => $this->in->getString('query')]];
            }

            // Search form: status
            if ($search_term = $this->in->getCleanValueArray('search_status', 'string', 'discard')) {
                $terms[] = ['type' => 'status', 'op' => 'is', 'options' => ['status' => $search_term]];
            }

            // Search form: search_assigned
            if ($search_term = $this->in->getCleanValueArray('search_assigned', 'raw', 'discard')) {
                $agent_ids = [];
                $team_ids  = [];

                foreach ($search_term as $t) {
                    if (strpos($t, 'team.') === 0) {
                        $t = Strings::extractRegexMatch('#^team\.(\d+)$#', $t);
                        if ($t !== '') {
                            $t          = (int) $t;
                            $team_ids[] = $t;
                        }
                    } else {
                        $t           = (int) $t;
                        $agent_ids[] = $t;
                    }
                }

                $agent_ids = array_unique($agent_ids);
                $team_ids  = array_unique($team_ids);

                if ($agent_ids) {
                    $terms[] = ['type' => 'agent', 'op' => 'is', 'options' => ['agent_ids' => $agent_ids]];
                }
                if ($team_ids) {
                    $terms[] = ['type' => 'agent_team', 'op' => 'is', 'options' => ['team_ids' => $team_ids]];
                }
            }

            if ($search_person_id = $this->in->getUInt('search_person_id')) {
                $terms[] = ['type' => 'person_id', 'op' => 'is', 'options' => ['person_id' => $search_person_id]];
            }

            // Search form: status
            if ($search_term = $this->in->getCleanValueArray('search_status', 'string', 'discard')) {
                $terms[] = ['type' => 'status', 'op' => 'contains', 'options' => ['status' => $search_term]];
            }

            // Search form: subject
            $search_term = $this->in->getCleanValueArray('search_subject_string', 'string', 'discard');
            if ($search_term && $search_term[0]) {
                foreach ($search_term as $k => $string) {
                    $op   = $this->in->getString("search_subject_op.$k");
                    $type = $this->in->getString("search_subject_type.$k");

                    $terms[] = ['type' => 'subject_adv', 'op' => $op, 'options' => ['query' => $string, 'type' => $type]];
                }
            } elseif ($search_term = $this->in->getString('search_subject_simple')) {
                $terms[] = ['type' => 'subject', 'op' => 'contains', 'options' => ['query' => $search_term]];
            }

            // Search form: message
            $search_term = $this->in->getCleanValueArray('search_message_string', 'string', 'discard');
            if ($search_term && $search_term[0]) {
                foreach ($search_term as $k => $string) {
                    $op   = $this->in->getString("search_message_op.$k");
                    $type = $this->in->getString("search_message_type.$k");
                    $who  = $this->in->getString("search_message_who.$k");

                    $date = null;
                    if ($date_op = $this->in->getString("search_message_when_op.$k")) {
                        $date = [
                            'date1'               => $this->in->getString("search_message_when.date1.$k"),
                            'date2'               => $this->in->getString("search_message_when.date2.$k"),
                            'date1_relative'      => $this->in->getString("search_message_when.date1_relative.$k"),
                            'date2_relative'      => $this->in->getString("search_message_when.date2_relative.$k"),
                            'date1_relative_type' => $this->in->getString("search_message_when.date1_relative_type.$k"),
                            'date2_relative_type' => $this->in->getString("search_message_when.date2_relative_type.$k"),
                        ];
                    }

                    $terms[] = [
                        'type'    => 'ticket_message_adv',
                        'op'      => $op,
                        'options' => [
                            'query'   => $string,
                            'type'    => $type,
                            'who'     => $who,
                            'date'    => $date,
                            'date_op' => $date_op,
                        ], ];
                }
            } elseif ($search_term = $this->in->getString('search_message_simple')) {
                $terms[] = ['type' => 'ticket_message', 'op' => 'contains', 'options' => ['query' => $search_term]];
            }

            $do_run = true;
        }

        //------------------------------
        // Re-do search if we changed order
        //------------------------------

        if ($result_cache && $order_by && $result_cache->getExtraData('order_by') != $order_by) {
            $terms  = $result_cache['criteria'];
            $do_run = true;
        }

        //------------------------------
        // Run a filter if we need to
        //------------------------------

        if ($do_run) {
            $searcher = new TicketSearch();
            $searcher->setPerson($this->person);
            if ($order_by) {
                $searcher->setOrderByCode($order_by);
            }

            $user_searcher  = new \Application\DeskPRO\Searcher\PersonSearch();
            $org_searcher   = new \Application\DeskPRO\Searcher\OrganizationSearch();
            $has_user_terms = false;
            $has_org_terms  = false;

            foreach ($terms as $term) {
                if (empty($term['op'])) {
                    continue;
                }
                if (!isset($term['options'])) {
                    $term['options'] = [];
                }
                if (strpos($term['type'], 'person_') === 0 && $term['type'] != 'person_id') {
                    $user_searcher->addTerm($term['type'], $term['op'], $term['options']);
                    $has_user_terms = true;
                } elseif (strpos($term['type'], 'org_') === 0) {
                    $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                    $has_org_terms = true;
                } else {
                    $searcher->addTerm($term['type'], $term['op'], $term['options']);
                }
            }

            if ($has_user_terms) {
                $searcher->setPersonSearch($user_searcher);
            }
            if ($has_org_terms) {
                $searcher->setOrganizationSearch($org_searcher);
            }

            $results = $searcher->getMatches();
            $results = Arrays::castToType($results, 'integer');

            if (!$result_cache) {
                $result_cache         = new \Application\DeskPRO\Entity\ResultCache();
                $result_cache->person = $this->person;
            }

            $needs_urgency = $searcher->needsUrgency();

            $result_cache->results     = $results;
            $result_cache->criteria    = $terms;
            $result_cache->num_results = count($results);
            $result_cache->setExtraData('order_by', $order_by);
            $result_cache->setExtraData('needs_urgency', $needs_urgency);
            $result_cache->setExtraData('terms_summary', $searcher->getSummary());
            $result_cache->setExtraData('order_by_summary', $searcher->getOrderBySummary());

            $this->em->persist($result_cache);
            $this->em->flush();
        }

        $helper = Helper\TicketResults::newFromResultCache($this, $result_cache);
        if ($group_by) {
            $helper->setGroupField($group_by);
        }

        $vars = [
            'cache'            => $result_cache,
            'cache_id'         => $result_cache->id,
            'order_by_summary' => $result_cache->getExtraData('order_by_summary'),
            'terms_summary'    => $result_cache->getExtraData('terms_summary'),
            'needs_urgency'    => $result_cache->getExtraData('needs_urgency'),
            'order_by'         => explode(':', $result_cache->getExtraData('order_by')),
            'ticket_ids'       => $result_cache->results,
            'view_name'        => $this->in->getString('view_name'),
            'view_extra'       => $this->in->getString('view_extra'),
        ];

        $search_form = [
            'terms'    => $result_cache->criteria,
            'order_by' => $result_cache->getExtraData('order_by'),
        ];
        $vars['search_form'] = $search_form;

        if ($this->in->getString('filtername')) {
            $vars['filtername'] = $this->in->getString('filtername');
        }

        if ($this->in->getString('view_name')) {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.name_'.$this->in->getString('view_name'));
        } else {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-basic-display-fields.general');
        }

        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            // Default display fields based on the filter
            $vars['display_fields'] = $this->_suggestedDisplayFields($searcher);
        }

        return $this->_getResponseForTickets('custom-filter', $result_cache['id'], $helper, $vars);
    }

    public function runFilterAction($filter_id)
    {
        if ($this->get('deskpro.feature_flags')->hasBeta('new_filters')) {
            return $this->runNewFilterAction($filter_id);
        } else {
            return $this->runLegacyFilterAction($filter_id);
        }
    }

    public function runNewFilterAction($filter_id)
    {
        $view_type = $this->in->getString('view_type');

        $ticketFilter = $this->get('doctrine.orm.entity_manager')->find(TicketFilter::class, $filter_id);
        if (!$ticketFilter) {
            throw $this->createNotFoundException();
        }

        $legacy_order_by = $this->in->getString('order_by');
        if (!$legacy_order_by) {
            $legacy_order_by = $this->person->getPref('agent.ui.ticket-filter-order-by.'.$ticketFilter->getId());
        }
        if (!$legacy_order_by) {
            $legacy_order_by = 'ticket.date_created:desc';
        }

        $legacy_order_by = explode(':', $legacy_order_by);
        if (!isset($legacy_order_by[1])) {
            $legacy_order_by[1] = 'desc';
        }

        $set_group_term   = null;
        $set_group_option = null;
        if ($this->in->getString('subFilterBy')) {
            $set_group_term   = $this->in->getString('subFilterBy');
            $set_group_option = $this->in->getString('subFilterByValue');
        } elseif ($this->in->getString('set_group_term')) {
            // legacy name for this param
            $set_group_term   = $this->in->getString('set_group_term');
            $set_group_option = $this->in->getString('set_group_option');
        }

        $ticketFilters = $this->container->get('ticketfilters');

        try {
            $query = $ticketFilters->getFilterQuery($ticketFilter->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get filter model');
        }

        try {
            $context = $ticketFilters->getAgentContext($this->person->getId());
        } catch (\OutOfBoundsException $e) {
            throw $this->createNotFoundException('failed to get agent model');
        }

        $searchParams = $ticketFilters->createSearchParams();
        if ($legacy_order_by) {
            if (!in_array($legacy_order_by[0], $searchParams->getAvailableOrderFields())) {
                $legacy_order_by[0] = TicketSearchParams::ORDER_DATE_CREATED;
            }
            $searchParams->orderBy($legacy_order_by[0], $legacy_order_by[1]);
        }

        if ($set_group_term) {
            $searchParams->subFilterBy($set_group_term, $set_group_option);
        }

        $searcher = $ticketFilters->getSearcher();
        $qb       = $searcher
            ->getIdsQueryBuilder($query, $context, $searchParams)
            ->setFirstResult(0)
            ->setMaxResults(10000);

        $results = $qb->execute()->fetchAll(\PDO::FETCH_COLUMN);
        $results = ListUtils::map($results, function ($v) {
            return (int) $v;
        });

        $helper = new Helper\TicketResults($this);
        $helper->setTicketIds($results);

        // Or if the user has their own
        $group_by = $this->person->getPref('agent.ui.ticket-filter-group-by.'.$ticketFilter->getId());

        if ($this->in->checkIsset('group_by')) {
            $group_by = $this->in->getString('group_by');

            App::getEntityRepository(PersonPref::class)->savePref(
                $this->person,
                'agent.ui.ticket-filter-group-by.'.$ticketFilter->getId(),
                $group_by
            );
        }

        if ($group_by) {
            $helper->setGroupField($group_by);
        }

        $vars = [
            'filter'           => $ticketFilter,
            'filter_id'        => $ticketFilter->getId(),
            'needs_urgency'    => true,
            'order_by_summary' => implode(':', $legacy_order_by),
            'set_group_term'   => $set_group_term,
            'set_group_option' => $set_group_option,
            'ticket_ids'       => $results,
            'order_by'         => $legacy_order_by,
            'is_new_filters'   => true,
        ];

        $pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.'.$ticketFilter->getId());
        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            // Default display fields based on the filter
            $vars['display_fields'] = $this->_suggestedDisplayFields(null);
        }

        return $this->_getResponseForTickets('filter', $ticketFilter->getId(), $helper, $vars);
    }

    public function runLegacyFilterAction($filter_id)
    {
        $view_type = $this->in->getString('view_type');

        /** @var $filter \Application\DeskPRO\Entity\LegacyTicketFilter */
        $filter = $this->em->getRepository(LegacyTicketFilter::class)->find($filter_id ?: 0);

        if (!$filter) {
            throw $this->createNotFoundException();
        }

        $searcher = $filter->getSearcher();
        $searcher->setPerson($this->person);

        $order_by = $this->in->getString('order_by');
        if (!$order_by) {
            $order_by = $this->person->getPref('agent.ui.ticket-filter-order-by.'.$filter['id']);
        }

        if (!$order_by and $filter['order_by']) {
            $order_by = $filter['order_by'];
        }

        if ($order_by) {
            $searcher->setOrderByCode($order_by);
        }

        if ($view_type == 'csv') {
            $searcher->setLimit(0);
        }

        $set_group_term   = null;
        $set_group_option = null;
        if ($this->in->getString('set_group_term')) {
            $set_group_term   = $this->in->getString('set_group_term');
            $set_group_option = $this->in->getString('set_group_option');

            $term = GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
            if ($term) {
                $type   = $term['type'];
                $op     = $term['op'];
                $choice = $term;
                unset($choice['type'], $choice['op']);

                $searcher->addTerm($type, $op, $choice);
            }
        }

        $results = $searcher->getMatches();

        $results = Arrays::castToType($results, 'integer');

        $helper = new Helper\TicketResults($this);
        $helper->setTicketIds($results);

        if ($order_by) {
            $helper->setGroupOrderBy($order_by);
        }

        // Or if the user has their own
        $group_by = $this->person->getPref('agent.ui.ticket-filter-group-by.'.$filter['id']);

        if ($this->in->checkIsset('group_by')) {
            $group_by = $this->in->getString('group_by');

            App::getEntityRepository(PersonPref::class)->savePref(
                $this->person,
                'agent.ui.ticket-filter-group-by.'.$filter['id'],
                $group_by
            );
        } elseif ($filter['group_by']) {
            $group_by = $filter['group_by'];
        }

        if ($group_by) {
            $helper->setGroupField($group_by);
        }

        $needs_urgency = $searcher->needsUrgency();

        $vars = [
            'filter'           => $filter,
            'filter_id'        => $filter['id'],
            'needs_urgency'    => $needs_urgency,
            'order_by_summary' => $searcher->getOrderBySummary(),
            'terms_summary'    => $searcher->getSummary(),
            'set_group_term'   => $set_group_term,
            'set_group_option' => $set_group_option,
            'ticket_ids'       => $results,
            'order_by'         => $searcher->getOrderBy(),
        ];

        $pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.'.$filter['id']);
        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            // Default display fields based on the filter
            $vars['display_fields'] = $this->_suggestedDisplayFields($filter->getSearcher());
        }

        $search_form = [
            'terms'    => $filter['terms'],
            'order_by' => $filter['order_by'],
        ];
        $vars['search_form'] = $search_form;

        return $this->_getResponseForTickets('filter', $filter->getId(), $helper, $vars);
    }

    public function runNamedFilterAction($filter_name)
    {
        $filter = $this->em->getRepository(LegacyTicketFilter::class)->findOneBy(['sys_name' => $filter_name]);
        if (!$filter) {
            throw $this->createNotFoundException();
        }

        return $this->runFilterAction($filter['id']);
    }

    public function runSlaAction($sla_id, $sla_status = '')
    {
        $view_type = $this->in->getString('view_type');

        /** @var $sla \Application\DeskPRO\Entity\Sla */
        $sla = $this->em->getRepository(Sla::class)->find($sla_id);

        if (!$sla) {
            throw $this->createNotFoundException();
        }

        $searcher = new TicketSearch();
        $searcher->setPerson($this->person);

        $sla_filter = $this->person->getPref('agent.ui.sla.ticket-filter', 'all');
        if ($sla_filter == 'agent') {
            $searcher->addTerm(TicketSearch::TERM_AGENT, 'is', $this->person->id);
        } elseif ($sla_filter == 'team') {
            $searcher->addTerm(TicketSearch::TERM_AGENT_TEAM, 'is', $this->person->getAgentTeamIds());
        }

        $searcher->addTerm(TicketSearch::TERM_SLA_COMPLETED, 'is', [
            'is_completed' => 0,
            'sla_id'       => $sla_id,
        ]);

        if ($sla_status) {
            $searcher->addTerm(TicketSearch::TERM_SLA_STATUS, 'is', [
                'sla_status' => $sla_status,
                'sla_id'     => $sla_id,
            ]);
        }

        if ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_WAITING_TIME) {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
        } elseif ($sla->sla_type == \Application\DeskPRO\Entity\Sla::TYPE_FIRST_RESPONSE) {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', 'awaiting_agent');
        } else {
            $searcher->addTerm(TicketSearch::TERM_STATUS, 'is', ['awaiting_agent', 'awaiting_user']);
        }

        $order_by = $this->in->getString('order_by');
        if (!$order_by) {
            $order_by = $this->person->getPref('agent.ui.ticket-sla-order-by.'.$sla['id'], 'ticket.sla_severity:desc');
        }

        if ($order_by) {
            $searcher->setOrderByCode($order_by);
        }

        if ($view_type == 'csv') {
            $searcher->setLimit(0);
        }

        $set_group_term   = null;
        $set_group_option = null;
        if ($this->in->getString('set_group_term')) {
            $set_group_term   = $this->in->getString('set_group_term');
            $set_group_option = $this->in->getString('set_group_option');

            $term = GroupingCounter::getSearchTerm($set_group_term, $set_group_option);
            if ($term) {
                $type   = $term['type'];
                $op     = $term['op'];
                $choice = $term;
                unset($choice['type'], $choice['op']);

                $searcher->addTerm($type, $op, $choice);
            }
        }

        // if no sla status was provided then count by sla status
        // to update sla badge counts real-time
        $slaGroupCounts = [];
        if (!$sla_status) {
            foreach (['ok', 'warning', 'fail'] as $groupStatus) {
                $groupSearcher = clone $searcher;
                $groupSearcher->addTerm(TicketSearch::TERM_SLA_STATUS, 'is', [
                    'sla_status' => $groupStatus,
                    'sla_id'     => $sla->getId(),
                ]);

                $slaGroupCounts[$groupStatus] = $groupSearcher->getCount();
            }
        }

        $results = $searcher->getMatches();
        $results = Arrays::castToType($results, 'integer');

        $helper = new Helper\TicketResults($this);
        $helper->setTicketIds($results);

        // Or if the user has their own
        $group_by = $this->person->getPref('agent.ui.ticket-sla-group-by.'.$sla['id']);

        if ($this->in->getString('group_by')) {
            $group_by = $this->in->getString('group_by');

            App::getEntityRepository(PersonPref::class)->savePref(
                $this->person,
                'agent.ui.ticket-sla-group-by.'.$sla['id'],
                $group_by
            );
        }

        if ($group_by) {
            $helper->setGroupField($group_by);
        }

        $needs_urgency = $searcher->needsUrgency();

        $vars = [
            'sla'              => $sla,
            'sla_id'           => $sla->id,
            'sla_status'       => $sla_status,
            'sla_filter'       => $sla_filter,
            'needs_urgency'    => $needs_urgency,
            'order_by_summary' => $searcher->getOrderBySummary(),
            'terms_summary'    => $searcher->getSummary(),
            'set_group_term'   => $set_group_term,
            'set_group_option' => $set_group_option,
            'ticket_ids'       => $results,
            'order_by'         => $searcher->getOrderBy(),
            'sla_group_counts' => $slaGroupCounts,
        ];

        $pref_display_fields = $this->person->getPref('agent.ui.ticket-sla-display-fields.'.$sla['id']);
        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            // Default display fields based on the filter
            $vars['display_fields'] = $this->_suggestedDisplayFields($searcher);
            if (!in_array('slas', $vars['display_fields'])) {
                $vars['display_fields'][] = 'slas';
            }
        }

        return $this->_getResponseForTickets('sla', $sla['id'], $helper, $vars);
    }

    /**
     * @param string               $type
     * @param                      $type_id
     * @param Helper\TicketResults $results_helper
     * @param array                $vars
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function _getResponseForTickets($type, $type_id, $results_helper, array $vars = [])
    {
        $view_type = $this->in->getString('view_type');
        if (!$view_type or !in_array($view_type, ['list', 'simple', 'simple-ext', 'csv', 'json'])) {
            $view_type = 'simple-ext';
        }

        $is_partial = false;
        $tpl        = 'AgentBundle:TicketSearch:filter-results-'.$view_type.'.html.twig';
        if ($this->in->getBool('partial')) {
            $is_partial = true;
            $tpl        = 'AgentBundle:TicketSearch:part-results-'.$view_type.'.html.twig';
        }

        $per_page = 50;
        if ($view_type == 'list') {
            $per_page = 50;
        }

        $vars['viewtpl'] = $type;

        //------------------------------
        // Get the tickets to show
        //------------------------------

        $grouped_info = null;
        if (!$is_partial and $results_helper->isGroupable()) {
            $grouped_info = $results_helper->getGroupDisplayInfo();
        }

        $page = $this->in->getUInt('page');
        if (!$page) {
            $page = 1;
        }

        $cursor = $this->in->getUInt('cursor');
        if (!$cursor) {
            $cursor = 0;
        }

        /** @var Ticket[] $tickets */
        $tickets = [];

        if (!$this->in->checkIsset('grouping_option') || $this->in->getString('grouping_option') == '-1' || $this->in->getString('grouping_option') == 'DP_NOT_SET') {
            // User looking at all results
            $is_grouping     = false;
            $grouping_option = 'DP_NOT_SET';
            if ($view_type != 'csv') {
                if ($cursor) {
                    $tickets = $results_helper->getTicketsForCursorPage($cursor, $per_page);
                } else {
                    $tickets = $results_helper->getTicketsForPage($page, $per_page);
                }
            }
        } else {
            // User looking at just a group of results
            $is_grouping     = true;
            $grouping_option = $this->in->getString('grouping_option');
            if ($grouping_option == 'DP_NOT_SET') {
                $grouping_option = -1;
            }

            if ($view_type != 'csv') {
                if ($cursor) {
                    $tickets = $results_helper->getGroupedTicketsForCursorPage($grouping_option, $cursor, $per_page);
                } else {
                    $tickets = $results_helper->getGroupedTicketsForPage($grouping_option, $page, $per_page);
                }

                $vars['ticket_ids'] = $results_helper->getGroupTicketIds($grouping_option);
            }
        }

        //------------------------------
        // Send results
        //------------------------------

        if (!count($tickets) && $is_partial && $view_type != 'csv') {
            return $this->createJsonResponse(['no_more_results' => true]);
        }

        if ($view_type != 'csv') {
            $flagged_tickets = $this->em->getRepository(TicketFlagged::class)->getFlagsForTickets($tickets, $this->person);
        } else {
            $flagged_tickets = [];
        }

        if (empty($vars['display_fields'])) {
            $vars['display_fields'] = ['date_created', 'department'];
        }

        /** @var TicketFields $customTicketFieldsHandler */
        $customTicketFieldsHandler = App::getApi('custom_fields.tickets');
        /** @var PeopleFields $customPersonFieldsHandler */
        $customPersonFieldsHandler = App::getApi('custom_fields.people');

        // ticket and person defs for columns
        $ticket_field_defs = $customTicketFieldsHandler->getEnabledFields();
        $person_field_defs = $customPersonFieldsHandler->getEnabledFields();

        $vars['display_fields'] = array_unique($vars['display_fields']);

        $pageinfo = Numbers::getPaginationPages($results_helper->getCount(), $page, $per_page);

        $has_t_fields = false;
        $has_u_fields = false;

        foreach ($vars['display_fields'] as $f) {
            if (strpos($f, 'ticket_fields[') === 0) {
                $has_t_fields = true;
            }
            if (strpos($f, 'person_fields[') === 0) {
                $has_u_fields = true;
            }
        }

        $all_custom_fields      = [];
        $user_all_custom_fields = [];

        if ($has_t_fields || $has_u_fields) {
            $field_manager      = $this->container->getSystemService('ticket_fields_manager');
            $user_field_manager = $this->container->getSystemService('person_fields_manager');

            foreach ($tickets as $ticket) {
                if ($has_t_fields) {
                    $all_custom_fields[$ticket->getId()] = $field_manager->getDisplayArrayForObject($ticket);
                }

                if ($has_u_fields) {
                    $person                                   = $ticket->getPerson();
                    $user_all_custom_fields[$person->getId()] = $user_field_manager->getDisplayArrayForObject($person);
                }
            }
        }

        $ticket_display = new TicketResultsDisplay($tickets);
        $ticket_display->setPersonContext($this->person);

        $json_renderer = new TicketListRenderer($ticket_display);

        $vars['display_fields'] = $this->normalizeDisplayFields(!empty($vars['display_fields']) ? $vars['display_fields'] : []);

        $vars = array_merge($vars, [
            'type'                   => $type,
            'type_id'                => $type_id,
            'ticket_display'         => $ticket_display,
            'tickets'                => $tickets,
            'all_ticket_ids'         => $is_grouping ? $results_helper->getGroupTicketIds($grouping_option) : $results_helper->getTicketIds(),
            'count'                  => $results_helper->getCount(),
            'flagged_tickets'        => $flagged_tickets,
            'page'                   => $page,
            'pageinfo'               => $pageinfo,
            'per_page'               => $per_page,
            'show_flag'              => true,
            'grouped_info'           => $grouped_info,
            'group_by'               => $results_helper->getGroupField(),
            'grouping_option'        => $grouping_option,
            'grouping_summary'       => $results_helper->getGroupingSummary(),
            'is_grouped_result'      => $is_grouping,
            'ticket_field_defs'      => $ticket_field_defs,
            'person_field_defs'      => $person_field_defs,
            'load_first'             => $this->in->getBool('load_first'),
            'all_custom_fields'      => $all_custom_fields,
            'user_all_custom_fields' => $user_all_custom_fields,
        ]);

        if ($view_type == 'csv') {
            return $this->_outputCsv($vars, $results_helper);
        }

        if ($view_type == 'json') {
            return $this->createJsonResponse([
                'tickets'        => $json_renderer->renderTicketDisplayArray(),
                'all_ticket_ids' => $vars['all_ticket_ids'],
            ]);
        } else {
            $vars['ticket_json'] = $json_renderer->renderTicketDisplayJson();

            $html = $this->renderView($tpl, $vars);

            if ($is_partial) {
                return $this->createJsonResponse([
                    'html'              => $html,
                    'page'              => $page,
                    'is_grouped_result' => $is_grouping,
                ]);
            } else {
                return $this->createResponse($html);
            }
        }
    }

    protected function _outputCsv(array $vars, TicketResults $results_helper)
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="tickets.csv"');

        $display_fields = [
            'id',
            'language_id',
            'department_id',
            'category_id',
            'priority_id',
            'workflow_id',
            'product_id',
            'person_id',
            'person_email_id',
            'agent_id',
            'agent_team_id',
            'organization_id',
            'linked_chat_id',
            'email_account_id',
            'locked_by_agent',
            'ref',
            'auth',
            'creation_system',
            'ticket_hash',
            'status',
            'hidden_status',
            'is_hold',
            'urgency',
            'date_created',
            'date_resolved',
            'date_archived',
            'date_first_agent_assign',
            'date_first_agent_reply',
            'date_last_agent_reply',
            'date_last_user_reply',
            'date_agent_waiting',
            'date_user_waiting',
            'date_status',
            'total_user_waiting',
            'total_to_first_reply',
            'date_locked',
            'has_attachments',
            'subject',
            'labels',
        ];

        $field_manager = $this->container->getSystemService('ticket_fields_manager');
        foreach ($field_manager->getFields() as $f) {
            $display_fields[] = 'ticket_fields['.$f->id.']';
        }

        $temp = fopen('php://memory', 'rw');
        $row  = [];

        foreach ($display_fields as $display_field) {
            switch ($display_field) {
                case 'id':
                    $row[] = 'ticket_id';
                    break;
                case 'language_id':
                case 'department_id':
                case 'priority_id':
                case 'category_id':
                case 'workflow_id':
                case 'product_id':
                case 'email_account_id':
                    $row[] = $display_field;
                    $row[] = preg_replace('/id$/', 'title', $display_field);
                    break;
                case 'person_id':
                case 'agent_id':
                case 'agent_team_id':
                case 'organization_id':
                    $row[] = $display_field;
                    $row[] = preg_replace('/id$/', 'name', $display_field);
                    break;
                default:
                    if ($field_id = Strings::extractRegexMatch('#^ticket_fields\[(\d+)\]$#', $display_field)) {
                        $row[] = $field_manager->getFieldFromId($field_id)->title;
                    } else {
                        $row[] = $display_field;
                    }
                    break;
            }
        }

        fputcsv($temp, $row);
        rewind($temp);
        $response->setContent(fgets($temp));
        ftruncate($temp, 0);
        $chunk_size = 1024;
        $page       = 1;

        // This behaves unexpectly. If the total number of tickets is less than the page size it will always return all
        // of the tickets regardless of the page setting.
        if ($vars['is_grouped_result']) {
            $tickets = $results_helper->getGroupedTicketsForPage($this->in->getString('grouping_option'), $page, $chunk_size);
        } else {
            $tickets = $results_helper->getTicketsForPage($page, $chunk_size);
        }
        $vars['ticket_display'] = new TicketResultsDisplay($tickets);
        $vars['ticket_display']->setPersonContext($this->person);

        while (!empty($tickets)) {
            $ticket           = array_shift($tickets);
            $custom_text_data = $field_manager->getRenderedToTextForObject($ticket);
            $row              = [];

            foreach ($display_fields as $display_field) {
                switch ($display_field) {
                    case 'language_id':
                    case 'department_id':
                    case 'priority_id':
                    case 'category_id':
                    case 'workflow_id':
                    case 'product_id':
                    case 'email_account_id':
                        preg_match('/^(.*)_id$/', $display_field, $matches);
                        list(, $name) = $matches;
                        $entity       = null;

                        if (isset($ticket[$name])) {
                            $entity = $ticket->{$name};
                        }

                        if ($entity) {
                            if ($display_field == 'email_account_id') {
                                $row[] = $entity->id;
                                $row[] = $entity->address;
                            } elseif ($entity) {
                                $row[] = $entity->id;
                                $row[] = $entity->title;
                            }
                        } else {
                            $row[] = $row[] = '';
                        }
                        break;
                    case 'person_id':
                    case 'agent_id':
                        preg_match('/^(.*)_id$/', $display_field, $matches);
                        list(, $name) = $matches;
                        $entity       = $ticket->{$name};

                        if ($entity) {
                            $row[] = $entity->id;
                            $row[] = $entity->display_name;
                        } else {
                            $row[] = '';
                            $row[] = '';
                        }
                        break;
                    case 'agent_team_id':
                    case 'organization_id':
                        preg_match('/^(.*)_id$/', $display_field, $matches);
                        list(, $name) = $matches;
                        $entity       = $ticket->{$name};

                        if ($entity) {
                            $row[] = $entity->id;
                            $row[] = $entity->name;
                        } else {
                            $row[] = '';
                            $row[] = '';
                        }
                        break;
                    case 'person_email_id':
                    case 'labels':
                        $row[] = implode('|', $vars['ticket_display']->getTicketLabels($ticket));
                        break;
                    default:
                        if ($field_id = Strings::extractRegexMatch('#^ticket_fields\[(\d+)\]$#', $display_field)) {
                            if (isset($custom_text_data[$field_id])) {
                                $row[] = $custom_text_data[$field_id]['rendered'];
                            } else {
                                $row[] = '';
                            }
                        } elseif (preg_match('/^(.*)_id$/', $display_field, $matches)) {
                            list(, $name) = $matches;
                            $entity       = $ticket->{$name};

                            if ($entity) {
                                $row[] = $entity->id;
                            } else {
                                $row[] = '';
                            }
                        } else {
                            if (isset($ticket[$display_field])) {
                                $value = $ticket[$display_field];
                            } else {
                                $value = null;
                            }

                            if (is_scalar($value)) {
                                $row[] = $value;
                            } elseif (is_object($value)) {
                                if ($value instanceof \DateTime) {
                                    $row[] = $value->format('c');
                                } else {
                                    $row[] = '';
                                }
                            } else {
                                $row[] = '';
                            }
                        }
                        break;
                }
            }

            fputcsv($temp, $row);
            rewind($temp);
            $response->setContent($response->getContent().fgets($temp));
            ftruncate($temp, 0);
        }

        fclose($temp);

        return $response;
    }

    public function getTicketRowsAction()
    {
        $ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
        $tickets    = $this->em->getRepository(Ticket::class)->getByIds($ticket_ids, true);

        $display_fields = [];
        $changed_fields = [];

        if ($macro_id = $this->in->getUInt('run_macro_id')) {
            $macro      = $this->em->find(TicketMacro::class, $macro_id);
            $actions    = null;
            $collection = $macro->getActionsCollection();

            foreach ($collection->getActions() as $action) {
                if ($action instanceof \Application\DeskPRO\Tickets\TicketActions\ActionInterface) {
                    $action->setMetaData(['is_preview' => true]);
                }
            }
        } else {
            $actions    = $this->in->getCleanValueArray('actions', 'raw', 'string');
            $collection = null;
        }

        if (($actions || $collection) && $tickets) {
            if (!$collection) {
                $factory    = new ActionsFactory();
                $collection = new ActionsCollection();
                foreach ($actions as $name => $opt) {
                    $action = $factory->createFromForm($name, $opt);
                    if ($action) {
                        if ($action instanceof \Application\DeskPRO\Tickets\TicketActions\ActionInterface) {
                            $action->setMetaData(['is_preview' => true]);
                        }

                        $collection->add($action);

                        $display_fields[] = $name;
                    }
                }
            }

            foreach ($tickets as $t) {
                $ticket_changes = $collection->getApplyActions($t, $this->person);
                if ($ticket_changes) {
                    $ticket_changed_fields = [];
                    foreach ($ticket_changes as $change) {
                        $ticket_changed_fields[$change['action']] = true;
                        $changed_fields[$t['id']]                 = $ticket_changed_fields;
                    }
                }
            }

            $display_fields = array_unique($display_fields);
        }

        $ticket_display = new TicketResultsDisplay($tickets);
        $ticket_display->setPersonContext($this->person);

        $json_renderer = new TicketListRenderer($ticket_display);

        if ($actions || $collection) {
            $ticket_data = $json_renderer->renderTicketDisplayArray(function (Entity\Ticket $ticket, array $data) use ($display_fields) {
                $data['force_display_fields'] = $display_fields;

                return $data;
            });
        } else {
            $ticket_data = $json_renderer->renderTicketDisplayArray();
        }

        return $this->createJsonResponse($ticket_data);
    }

    public function getSingleTicketRowAction($content_type, $content_id)
    {
        if ($content_type == 'sla') {
            $sla    = $this->em->getRepository(Sla::class)->find($content_id);
            $filter = null;
        } else {
            $filter = $this->em->getRepository(LegacyTicketFilter::class)->find($content_id);
            $sla    = null;
        }

        $ticket_id = $this->in->getUInt('ticket_id');
        $ticket    = $this->em->find(Ticket::class, $ticket_id);

        if (!$ticket) {
            return $this->createResponse('');
        }

        $vars = [
            'page'    => -1,
            'tickets' => [$ticket],
            'filter'  => $filter,
            'sla'     => $sla,
        ];

        $view_type = $this->in->getString('view_type');
        if (!$view_type or !in_array($view_type, ['list', 'simple', 'simple-ext'])) {
            $view_type = 'simple-ext';
        }

        if ($this->in->getString('view_name')) {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.name_'.$this->in->getString('view_name'));
        } elseif ($sla) {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-sla-display-fields.'.$sla['id']);
        } elseif ($filter) {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-filter-display-fields.'.$filter['id']);
        } else {
            $pref_display_fields = $this->person->getPref('agent.ui.ticket-basic-display-fields.general');
        }
        if ($pref_display_fields) {
            $vars['display_fields'] = $pref_display_fields;
        } else {
            // Default display fields based on the filter
            if ($filter) {
                $vars['display_fields'] = $this->_suggestedDisplayFields($filter->getSearcher());
            } else {
                $vars['display_fields'] = $this->_suggestedDisplayFields();
            }
        }

        $tpl = 'AgentBundle:TicketSearch:part-results-'.$view_type.'.html.twig';

        $ticket_display = new TicketResultsDisplay([$ticket->id => $ticket]);
        $ticket_display->setPersonContext($this->person);
        $vars['ticket_display'] = $ticket_display;

        return $this->render($tpl, $vars);
    }

    protected function _suggestedDisplayFields(TicketSearch $searcher = null)
    {
        $display_fields = ['subject', 'user', 'department', 'agent', 'agent_team'];

        if ($searcher) {
            $specific_fields = $searcher->getSpecificFields();

            $max = 5;
            foreach ($searcher->getTermFields() as $term) {
                if (!in_array($term, $specific_fields)) {
                    $display_fields[] = $term;
                }

                $display_fields = array_unique($display_fields);
                if (count($display_fields) >= $max) {
                    break;
                }
            }
        }

        return $display_fields;
    }

    //###########################################################################
    // ajax-release-locks
    //###########################################################################

    public function ajaxReleaseLocksAction()
    {
        $ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
        $tickets    = $this->em->getRepository(Ticket::class)->getTicketsFromIds($ticket_ids);

        $this->em->beginTransaction();

        foreach ($tickets as $ticket) {
            $ticket->unlockTicket();
            $this->em->persist($ticket);
        }

        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse(['success' => true]);
    }

    //###########################################################################
    // ajax-delete-ticket
    //###########################################################################

    public function ajaxDeleteTicketsAction()
    {
        $ticket_ids = $this->in->getCleanValueArray('ticket_ids', 'uint', 'discard');
        $tickets    = $this->em->getRepository(Ticket::class)->getTicketsFromIds($ticket_ids);

        $deleted_tickets = [];

        $this->em->beginTransaction();
        foreach ($tickets as $ticket) {
            $deleted_tickets[] = $ticket['id'];
            $this->em->remove($ticket);
        }
        $this->em->flush();
        $this->em->commit();

        return $this->createJsonResponse(['success' => true, 'deleted_tickets' => $deleted_tickets]);
    }

    //###########################################################################
    // ajax-get-macro-actions
    //###########################################################################

    public function ajaxGetMacroAction()
    {
        $macro_id = $this->in->getUInt('macro_id');
        $macro    = $this->em->getRepository(TicketMacro::class)->find($macro_id);

        $data                = [];
        $data['raw_actions'] = [];

        $raw_actions = $macro->getActionsArray();
        if (!empty($raw_actions['new_reply'])) {
            $data['raw_actions']['new_reply'] = $raw_actions['new_reply'];
        }

        return $this->createJsonResponse($data);
    }

    public function ajaxGetMacroActionsAction()
    {
        $macro_id = $this->in->getUInt('macro_id');
        $macro    = $this->em->getRepository(TicketMacro::class)->find($macro_id);

        $descriptions = $macro->getActionDescriptions();

        return $this->createJsonResponse([
            'macro_id'      => $macro['id'],
            'macro_actions' => $macro['actions'],
            'descriptions'  => $descriptions,
        ]);
    }

    public function ajaxSaveActionsAction()
    {
        $ticketIds = $this->in->getCleanValueArray('result_ids', 'uint', 'discard');

        // Accept changes to apply for previewing
        // - We just apply the changes but dont save them, they'll be
        //   properly displayed in the listing.
        $actions = $this->in->getCleanValueArray('actions', 'raw', 'string');

        $actionsBuilder = RuleBuilder::newTermsBuilder();
        $actionsSet     = $actionsBuilder->readForm($this->in->getCleanValueArray('actions_set', 'raw', 'raw'));

        $tickets = $this->em->getRepository(Ticket::class)->getTicketsResultsFromIds($ticketIds);

        foreach ($tickets as $t) {
            // disable auto processing because call to
            // $ticket->getTicketLogger()->done()
            // below will call it
            $t->disableAutoTicketProcess();
        }

        $macro = false;
        if ($macroId = $this->in->getUInt('run_macro_id')) {
            $macro = $this->em->find(TicketMacro::class, $macroId);
        }

        $permissionErrors = [];
        $validationErrors = [];
        $success          = [];

        if ($snippetIds = $this->in->getString('snippet_ids')) {
            $snippetIds = explode(',', $snippetIds);
            $snippetIds = array_map(function ($x) {
                return (int) trim($x);
            }, $snippetIds);
            $snippetIds = Arrays::removeFalsey($snippetIds);
            $snippetIds = array_unique($snippetIds, SORT_NUMERIC);
        } else {
            $snippetIds = [];
        }

        if (($actions || $actionsSet || $macro) && $tickets) {
            $ticketManager = $this->container->getTicketManager();
            $contextType   = 'update';

            if ($macro) {
                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    $actionsCollection = $macro->getActionsCollection($ticket);

                    if ($actionsCollection->hasActionType('Reply') || $actionsCollection->hasActionType('ReplySnippet')) {
                        $contextType = 'newreply';
                    } else {
                        $contextType = 'update';
                    }

                    $this->db->beginTransaction();
                    try {
                        if (!$actionsCollection->applyCheckPermission($ticket, $this->person)) {
                            $permissionErrors[] = $ticket->getId();
                            $this->db->rollback();
                            continue;
                        }

                        if ($macro) {
                            $macroLog = Entity\TicketObjectUseLog::createMacroLog($ticket, $this->getPerson(), $macro);
                            $this->em->persist($macroLog);
                        }

                        $actionsCollection->apply($ticket->getTicketLogger(), $ticket, $this->person);

                        if ($ticket->isResolved() && count($this->getTicketLayoutErrors($ticket))) {
                            $validationErrors[] = $ticket->getId();
                            $this->db->rollback();
                            continue;
                        }

                        $context = $ticketManager->createAgentExecutorContext($this->person, $contextType, 'web');
                        $ticketManager->saveTicket($ticket, $context);

                        $this->em->flush();
                        $this->db->commit();
                    } catch (\Exception $e) {
                        $this->db->rollback();
                        throw $e;
                    }
                }
            } else {
                $factory    = new ActionsFactory();
                $collection = new ActionsCollection();

                foreach ($actions as $name => $opt) {
                    // Cleanup RTE markup
                    if ($name == 'reply') {
                        $newMessage = isset($opt['reply_text']) ? $this->cleaner->clean($opt['reply_text'], 'html') : '';
                        $newMessage = Strings::trimHtml($newMessage);
                        $newMessage = Strings::prepareWysiwygHtml($newMessage);
                        $newMessage = RegexUtils::safePregReplace('#<img[^>]+class="dp-signature-image" alt="([^"]+)"[^>]*>#i', '$1', $newMessage);

                        if ($newMessage) {
                            $opt['reply_text'] = $newMessage;
                            $contextType       = 'newreply';
                        }
                    }
                    $action = $factory->createFromForm($name, $opt);
                    $collection->add($action);
                }

                foreach ($actionsSet as $info) {
                    $action = $factory->createFromForm($info['type'], $info['options']);
                    if ($action) {
                        $collection->add($action);
                    }
                }

                $collection->applyAllModifiers();

                $this->em->getConnection()->beginTransaction();
                /** @var Ticket $ticket */
                foreach ($tickets as $ticket) {
                    if (!$this->person->getPermissionsManager()->get('TicketChecker')->canView($ticket)) {
                        $permissionErrors[] = $ticket->getId();
                        continue;
                    }

                    try {
                        if (!$collection->applyCheckPermission($ticket, $this->person)) {
                            $permissionErrors[] = $ticket->getId();
                            continue;
                        }

                        if (count($this->getTicketLayoutErrors($ticket))
                            && $collection->hasActionType('Status')
                            && strpos($collection->getActionType('Status')->getFullStatus(), Ticket::STATUS_HIDDEN) === false
                        ) {
                            $validationErrors[] = $ticket->getId();
                            continue;
                        } else {
                            $collection->apply(null, $ticket, $this->person);
                        }

                        $context = $ticketManager->createAgentExecutorContext($this->person, $contextType, 'web');
                        $ticketManager->saveTicket($ticket, $context);

                        if ($snippetIds) {
                            foreach ($snippetIds as $snippetId) {
                                if ($this->container->get('deskpro.feature_flags')->hasBeta('new_snippets')) {
                                    // $snippetId refers here to the SnippetTranslation id
                                    $snippetTranslation = $this->em->find(SnippetTranslation::class, $snippetId);

                                    $messages = $ticket->getMessages();

                                    $message = $messages->last();

                                    if ($snippetTranslation) {
                                        $snippetLog = SnippetUseLog::createSnippetTicketLog($message, $this->getPerson(), $snippetTranslation);
                                        $snippet    = $snippetLog->getSnippet();
                                        $snippet->setUsageCount((int) $snippet->getUsageCount() + 1);
                                        $this->em->persist($snippet);
                                        $this->em->persist($snippetLog);
                                    }
                                } else {
                                    $snippet = $this->em->find(TextSnippet::class, $snippetId);

                                    if ($snippet) {
                                        $snippetLog = Entity\TicketObjectUseLog::createSnippetLog($ticket, $this->getPerson(), $snippet);
                                        $this->em->persist($snippetLog);
                                    }
                                }
                            }
                        }

                        $this->em->flush();

                        $success[] = $ticket->getId();
                    } catch (\Exception $e) {
                        $this->em->getConnection()->rollback();
                        throw $e;
                    }
                }
                $this->em->getConnection()->commit();
            }
        }

        $ticketData = null;
        if ($this->in->getBool('return_data')) {
            $ticketDisplay = new TicketResultsDisplay($tickets);
            $ticketDisplay->setPersonContext($this->person);

            $jsonRenderer = new TicketListRenderer($ticketDisplay);
            $ticketData   = $jsonRenderer->renderTicketDisplayArray();
        }

        return $this->createJsonResponse([
            'success'                   => true,
            'success_tickets'           => $success,
            'failed_tickets'            => $permissionErrors,
            'validation_failed_tickets' => $validationErrors,
            'client_messages'           => false,
            'ticket_data'               => $ticketData,
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getTicketMassActionOverlayAction()
    {
        $agents      = $this->em->getRepository(Person::class)->getAgents();
        $agent_teams = $this->em->getRepository(Entity\AgentTeam::class)->findAll();
        $brands      = $this->em->getRepository(Brand::class)->findAll();
        $macros      = $this->em->getRepository(TicketMacro::class)->getMacrosForPerson($this->person);

        /** @var TicketFields $customTicketFieldsHandler */
        $customTicketFieldsHandler = App::getApi('custom_fields.tickets');
        /** @var PeopleFields $customPersonFieldsHandler */
        $customPersonFieldsHandler = App::getApi('custom_fields.people');

        // ticket and person defs for columns
        $ticket_field_defs = $customTicketFieldsHandler->getEnabledFields();
        $person_field_defs = $customPersonFieldsHandler->getEnabledFields();

        /** @var Tickets $ticketsService */
        $ticketsService = App::getApi('tickets');
        $ticket_options = $ticketsService->getTicketOptions($this->person);

        $ticket_options['custom_ticket_fields'] = $customTicketFieldsHandler->getFieldsDisplayArray($ticket_field_defs);
        $ticket_options['people_organizations'] = $this->em->getRepository(Organization::class)->getOrganizationNames();
        $ticket_options['custom_people_fields'] = $customPersonFieldsHandler->getFieldsDisplayArray($person_field_defs);

        return $this->render('AgentBundle:TicketSearch:filter-massactions-overlay.html.twig', [
            'agents'               => $agents,
            'agent_teams'          => $agent_teams,
            'brands'               => $brands,
            'macros'               => $macros,
            'agent_signature'      => $this->person->getSignature(),
            'agent_signature_html' => $this->person->getSignatureHtml(),
            'ticket_options'       => $ticket_options,
        ]);
    }

    private function normalizeDisplayFields($display_fields)
    {
        if (!$display_fields || !is_array($display_fields)) {
            $display_fields = [];
        }

        if (!$this->container->getSetting('core_tickets.use_ref') && in_array('ref', $display_fields)) {
            $display_fields = Arrays::removeValue($display_fields, 'ref');
        }

        return array_values($display_fields);
    }

    /**
     * @param Ticket $ticket
     *
     * @return \Symfony\Component\Validator\ConstraintViolationListInterface
     */
    private function getTicketLayoutErrors(Ticket $ticket)
    {
        // use the importer validator as it's configured to use entity annotations as well
        // entity annotations are disabled in the basic agent validator
        $validator = $this->get('dp.importer_validator');
        $errors    = $validator->validate($ticket, [
            new AppAssert\Ticket\TicketLayout([
                'context' => 'agent',
            ]),
        ]);

        return $errors;
    }
}
