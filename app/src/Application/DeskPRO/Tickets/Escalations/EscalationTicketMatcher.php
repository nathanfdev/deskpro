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
 * @category Entities
 */

namespace Application\DeskPRO\Tickets\Escalations;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\TicketEscalation;
use Application\DeskPRO\Searcher\OrganizationSearch;
use Application\DeskPRO\Searcher\PersonSearch;
use Application\DeskPRO\Searcher\TicketSearch;
use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Psr\Log\NullLogger;

class EscalationTicketMatcher
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Application\DeskPRO\DBAL\Connection
     */
    private $db;

    /**
     * @var Logger
     */
    private $logger;

    public function __construct(EntityManager $em, Connection $db)
    {
        $this->em = $em;
        $this->db = $db;
        $this->logger = new NullLogger();
    }


    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }


    /**
     * @param  TicketEscalation                     $esc
     * @param  int                                  $limit
     * @return \Application\DeskPRO\Entity\Ticket[]
     */
    public function getMatches(TicketEscalation $esc, $limit = 100)
    {
        $this->logger->debug(sprintf("[EscalationTicketMatcher] Getting matches for %d %s -- limit(%d)", $esc->id, $esc->title, $limit));

        $ms_start = microtime(true);

        $searcher = $this->_getSearcherForEscalation($esc);
        $searcher->setLimit($limit);

        $this->logger->debug(sprintf("[EscalationTicketMatcher] --> SQL: %s", $searcher->getSql()));
        $ticket_ids = $searcher->getMatches();
        $tickets = array();
        if ($ticket_ids) {
            $tickets = $this->em->getRepository('DeskPRO:Ticket')->getByIds($ticket_ids);
        }
        $this->logger->debug(sprintf("[EscalationTicketMatcher] --> Number of results: %d", count($tickets)));
        $this->logger->debug(sprintf("[EscalationTicketMatcher] --> Took %.4fs", microtime(true) - $ms_start));

        return $tickets;
    }


    /**
     * @param  TicketEscalation          $esc
     * @return TicketSearch
     * @throws \InvalidArgumentException
     */
    private function _getSearcherForEscalation(TicketEscalation $esc)
    {
        $searcher = new TicketSearch();
        $searcher->addTerm('escalation_eliminator', 'is', array('escalation' => $esc));

        $user_searcher = new PersonSearch();
        $org_searcher  = new OrganizationSearch();

        $has_user_terms = false;
        $has_org_terms  = false;

        if ($esc->terms) {
            foreach ($esc->terms as $term) {
                if ($term['op'] != 'ignore') {
                    if (strpos($term['type'], 'person_') === 0) {
                        $user_searcher->addTerm($term['type'], $term['op'], $term['options']);
                        $has_user_terms = true;
                    } elseif (strpos($term['type'], 'org_') === 0) {
                        $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                        $has_org_terms = true;
                    } else {
                        $searcher->addTerm($term['type'], $term['op'], $term['options']);
                    }
                }
            }
        }
        if ($esc->terms_any) {
            foreach ($esc->terms_any as $term) {
                if ($term['op'] != 'ignore') {
                    if (strpos($term['type'], 'person_') === 0) {
                        $user_searcher->addAnyTerm($term['type'], $term['op'], $term['options']);
                        $has_user_terms = true;
                    } elseif (strpos($term['type'], 'org_') === 0) {
                        $org_searcher->addTerm($term['type'], $term['op'], $term['options']);
                        $has_org_terms = true;
                    } else {
                        $searcher->addAnyTerm($term['type'], $term['op'], $term['options']);
                    }
                }
            }
        }

        if ($has_user_terms) {
            $searcher->setPersonSearch($user_searcher);
        }
        if ($has_org_terms) {
            $searcher->setOrganizationSearch($org_searcher);
        }

        $searcher->addRawWhere("tickets.date_created >= '" . $esc->date_created->format('Y-m-d H:i:s') . "'");

        $time_secs = $esc->event_trigger_time;
        switch ($esc->event_trigger) {
            case TicketEscalation::EVENT_TYPE_TIME_OPEN:
                $searcher->addRawWhere('tickets.status IN (\'awaiting_user\', \'awaiting_agent\')');
                $date_cut = new \DateTime('-' . $time_secs . ' seconds');
                $searcher->addTerm('date_created', 'lte', array('date1' => $date_cut));

                break;

            case TicketEscalation::EVENT_TYPE_TIME_USER_WAITING:
                $searcher->addTerm('status', 'is', array('awaiting_agent'));
                $searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');

                $date_cut = new \DateTime('-' . $time_secs . ' seconds');
                $searcher->addTerm('user_waiting', 'lte', array('date1' => $date_cut));

                break;

            case TicketEscalation::EVENT_TYPE_TIME_TOTAL_USER_WAITING:
                $searcher->addTerm('status', 'is', array('awaiting_agent'));
                $searcher->addRawWhere('tickets.date_user_waiting IS NOT NULL');
                $searcher->addTerm('total_user_waiting', 'between', array($time_secs, $time_secs));
                break;

            case TicketEscalation::EVENT_TYPE_TIME_AGENT_WAITING:
                $searcher->addTerm('status', 'is', array('awaiting_user'));
                $searcher->addRawWhere('tickets.date_agent_waiting IS NOT NULL');

                $date_cut = new \DateTime('-' . $time_secs . ' seconds');
                $searcher->addTerm('agent_waiting', 'lte', array('date1' => $date_cut));

                break;

            case TicketEscalation::EVENT_TYPE_TIME_RESOLVED:
                $searcher->addTerm('status', 'is', array('resolved'));
                $searcher->addRawWhere('tickets.date_resolved IS NOT NULL');

                $date_cut = new \DateTime('-' . $time_secs . ' seconds');
                $searcher->addTerm('date_resolved', 'lte', array('date1' => $date_cut));

                break;

            default:
                throw new \InvalidArgumentException("Invalid escalation event: " . $esc->event_trigger);
        }

        return $searcher;
    }
}
