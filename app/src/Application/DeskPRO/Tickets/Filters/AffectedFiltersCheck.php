<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 */

namespace Application\DeskPRO\Tickets\Filters;

use Application\DeskPRO\Entity\Ticket;
use Monolog\Logger;
use Orb\Util\Arrays;

class AffectedFiltersCheck
{
    /**
     * @var Ticket
     */
    private $ticket;

    /**
     * @var \Application\DeskPRO\Entity\TicketFilter[]
     */
    private $filters;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var array
     */
    private $prev_field_versions;

    /**
     * @var array
     */
    private $field_versions;

    /**
     * @var \Application\DeskPRO\Entity\TicketFilter[]
     */
    private $affected_filters;

    /**
     * @var \Application\DeskPRO\Entity\TicketFilter[]
     */
    private $affected_filters_nochange;

    /**
     * @var bool
     */
    private $has_run = false;

    /**
     * @param Ticket $ticket
     * @param \Application\DeskPRO\Entity\TicketFilter[] $filters
     * @param Logger $logger
     */
    public function __construct(Ticket $ticket, array $filters, Logger $logger)
    {
        $this->ticket = $ticket;
        $this->filters = $filters;
        $this->logger = $logger;
    }

    /**
     * Runs through filters to see which might be affected by a change.
     */
    private function _run()
    {
        if ($this->has_run) {
            return;
        }
        $this->has_run = true;

        $this->logger->info(sprintf("[AffectedFilters] Checking %d filters", count($this->filters)));

        $state = $this->ticket->getStateChangeRecorder();

        $changed_fields = $state->getChangedFields();

        $this->field_versions = array();
        foreach ($changed_fields as $f) {
            $version = $state->getStateVersionForChange($state->getLastChangeForField($f));
            $this->field_versions[$f] = $version;
        }

        if ($this->prev_field_versions) {
            $new_changed_fields = array();
            $with_new_check = true;

            foreach ($this->field_versions as $f => $v) {
                if (!isset($this->prev_field_versions[$f]) || $this->prev_field_versions[$f] < $v) {
                    $new_changed_fields[] = $f;
                }
            }
        } else {
            $new_changed_fields = array();
            $with_new_check = false;
        }

        $this->logger->debug(sprintf("[AffectedFilters] Changed fields: %s", implode(', ', $changed_fields)));

        if ($with_new_check) {
            $this->logger->debug(sprintf("[AffectedFilters] New changed fields: %s", implode(', ', $new_changed_fields)));
        }

        // Convert the detected changed fields into names
        // the searcher defines
        $changed_fields = array_map(function ($field_name) {
            switch ($field_name) {
                case 'language': return 'ticket.language_id';
                case 'agent': return 'ticket.agent_id';
                case 'department': return 'ticket.department_id';
                case 'category': return 'ticket.category_id';
                case 'priority': return 'ticket.priority_id';
                case 'workflow': return 'ticket.workflow_id';
                case 'product': return 'ticket.product_id';
                case 'person': return 'ticket.person_id';
                case 'agent_team': return 'ticket.agent_team_id';
                case 'organization': return 'ticket.organization_id';
                default: return "ticket.$field_name";
            }
        }, $changed_fields);

        if ($changed_fields) {
            $changed_fields = array_combine($changed_fields, $changed_fields);
        }

        $is_hidden_change = false;
        if (isset($changed_fields['ticket.hidden_status'])) {
            $is_hidden_change = true;
        }

        $is_new_messages = false;
        if (isset($changed_fields['ticket.message'])) {
            $is_new_messages = true;
        }

        $affected_filters = array();
        $affected_filters_nochange = array();

        foreach ($this->filters as $f) {
            if ($is_new_messages || $is_hidden_change || $f->getSearcher()->hasAnyAffectedFields($changed_fields)) {
                $affected_filters[] = $f;

                // Do the same test again, but remove ones where previous state version
                if ($with_new_check && !$f->getSearcher()->hasAnyAffectedFields($new_changed_fields)) {
                    $affected_filters_nochange[] = $f;
                }
            }
        }

        $this->logger->info(sprintf("[AffectedFilters] %d filters with affected fields", count($affected_filters)));

        if ($with_new_check) {
            $this->logger->info(sprintf("[AffectedFilters] %d filters with affected fields but no changes since last run", count($affected_filters_nochange)));
        }

        $this->affected_filters_nochange = $affected_filters_nochange;
        $this->affected_filters = $affected_filters;
    }

    /**
     * Reset so the change detects are re-run on the latest state.
     */
    public function reset()
    {
        $this->has_run = false;
    }

    /**
     * Set previous state versions. This is used to determine if
     * a filter has not changed between two states.
     *
     * @param array $states
     */
    public function setPreviousFieldVersions(array $states)
    {
        $this->prev_field_versions = $states;
    }

    /**
     * Get the versions for fields used in the current affect change detection.
     *
     * @return array
     */
    public function getFieldVersions()
    {
        $this->_run();
        return $this->field_versions;
    }

    /**
     * Get an array of filters that are affected by the changed fields.
     *
     * @return \Application\DeskPRO\Entity\TicketFilter[]
     */
    public function getAffectedFilters()
    {
        $this->_run();
        return $this->affected_filters;
    }

    /**
     * Get an array of filters that are affected by the changed fields,
     * but have no new changes since the last set (see setPreviousFieldVersions).
     * Filters returned here can use the same result from any previous
     * run through the filter change detector.
     *
     * @return \Application\DeskPRO\Entity\TicketFilter[]
     */
    public function getAffectedFiltersWithNoChanges()
    {
        $this->_run();
        return $this->affected_filters_nochange;
    }

    /**
     * Return only filters that are affected by new changes. This is the
     * difference from getAffectedFilters/getAffectedFiltersWithNoChanges.
     *
     * @return \Application\DeskPRO\Entity\TicketFilter[]
     */
    public function getNewAffectedFilters()
    {
        $this->_run();
        if (!$this->affected_filters) {
            return array();
        } else if (!$this->affected_filters_nochange) {
            return $this->affected_filters;
        }

        return Arrays::arrayDiffIdentity($this->affected_filters, $this->affected_filters_nochange);
    }

    /**
     * Gets the newest set of field versions used during affected filter check.
     *
     * @return array
     */
    public function getNewestFieldVersions()
    {
        $this->_run();

        $ret = $this->field_versions;

        if ($this->prev_field_versions) {
            foreach ($this->prev_field_versions as $f => $v) {
                if (!isset($ret[$f]) || $v > $ret[$f]) {
                    $ret[$f] = $v;
                }
            }
        }

        return $ret;
    }
}