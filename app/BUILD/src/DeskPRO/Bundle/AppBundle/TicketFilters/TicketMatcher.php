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

namespace DeskPRO\Bundle\AppBundle\TicketFilters;

use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Context\AgentContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\Terms\TermsHandlerInterface;

class TicketMatcher
{
    /**
     * @var TermsHandlerInterface[]
     */
    private $handlers;

    /**
     * FieldId => Handler.
     *
     * @var TermsHandlerInterface[]
     */
    private $fieldToHandler;

    /**
     * TicketMatcher constructor.
     *
     * @param TermsHandlerInterface[] $handlers
     */
    public function __construct(array $handlers)
    {
        $this->handlers = $handlers;

        $this->fieldToHandler = [];
        foreach ($handlers as $h) {
            foreach ($h->getHandledFields() as $fid) {
                if (!isset($this->fieldToHandler[$fid])) {
                    $this->fieldToHandler[$fid] = [];
                }
                $this->fieldToHandler[$fid][] = $h;
            }
        }
    }

    public function doesQueryMatch(array $query, TicketModel $ticketModel, AgentContext $agentContext)
    {
        $rootPart = $query['query'];

        if ($rootPart['type'] === 'TERM_GROUP') {
            return $this->doesTermGroupMatch($rootPart, $ticketModel, $agentContext);
        } else {
            return $this->doesTermMatch($rootPart, $ticketModel, $agentContext);
        }
    }

    /**
     * @param array        $termGroup
     * @param TicketModel  $ticketModel
     * @param AgentContext $agentContext
     *
     * @return bool
     */
    private function doesTermGroupMatch(array $termGroup, TicketModel $ticketModel, AgentContext $agentContext)
    {
        if ($termGroup['type'] !== 'TERM_GROUP') {
            throw new \InvalidArgumentException('Only TERM_GROUP types can be matched');
        }

        $op       = $termGroup['operator'];
        $anyMatch = false;
        $anyFail  = false;

        foreach ($termGroup['terms'] as $term) {
            if ($term['type'] === 'TERM_GROUP') {
                if ($this->doesTermGroupMatch($term, $ticketModel, $agentContext)) {
                    $anyMatch = true;
                } else {
                    $anyFail = true;
                }
            } else {
                if ($this->doesTermMatch($term, $ticketModel, $agentContext)) {
                    $anyMatch = true;
                } else {
                    $anyFail = true;
                }
            }

            // possible return early
            if ($op === 'OR' && $anyMatch) {
                return true;
            } elseif ($op === 'AND' && $anyFail) {
                return false;
            }
        }

        if ($op === 'OR') {
            return $anyMatch;
        }

        if ($op === 'AND') {
            return $anyMatch && !$anyFail;
        }

        // not is actually interretted as NOT (AND)
        if ($op === 'NOT') {
            return !($anyMatch && !$anyFail);
        }

        throw new \InvalidArgumentException('Unknown operator');
    }

    /**
     * @param array        $term
     * @param TicketModel  $ticketModel
     * @param AgentContext $agentContext
     */
    public function doesTermMatch(array $term, TicketModel $ticketModel, AgentContext $agentContext)
    {
        if ($term['type'] !== 'TERM') {
            throw new \InvalidArgumentException('Only TERM types can be matched');
        }

        $fieldId = $term['field']['identity'];

        if (empty($this->fieldToHandler[$fieldId])) {
            throw new \OutOfBoundsException("No handler is capable of handling $fieldId");
        }

        foreach ($this->fieldToHandler[$fieldId] as $handler) {
            /** @var $handler TermsHandlerInterface */
            if ($handler->doesTicketMatch($term, $ticketModel, $agentContext)) {
                return true;
            }
        }

        return false;
    }
}
