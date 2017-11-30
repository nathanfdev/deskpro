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

namespace DeskPRO\Bundle\AppBundle\TicketFilters\Terms;

use Application\DeskPRO\Entity\TicketSla;
use DeskPRO\Bundle\AppBundle\TicketFilters\MatcherContext;
use DeskPRO\Bundle\AppBundle\TicketFilters\Model\Entity\TicketModel;
use DeskPRO\Bundle\AppBundle\TicketFilters\ValueResolver;
use DeskPRO\Component\FilterQueryLanguage\Query\Node\Term;
use DeskPRO\Component\FilterQueryLanguage\Query\Query;

class TicketSlaTermsHandler extends AbstractTermsHandler
{
    /**
     * {@inheritdoc}
     */
    public function getHandledFields()
    {
        return [
            Terms::TICKET_SLAS,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchFunctions()
    {
        return [
            ['name' => 'passingSlas', 'fields' => [Terms::TICKET_SLAS], 'method' => 'hasPassingSlas', 'operators' => [Query::OP_HAS]],
            ['name' => 'warningSlas', 'fields' => [Terms::TICKET_SLAS], 'method' => 'hasWarningSlas', 'operators' => [Query::OP_HAS]],
            ['name' => 'failedSlas',  'fields' => [Terms::TICKET_SLAS], 'method' => 'hasFailingSlas', 'operators' => [Query::OP_HAS]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function doesTicketMatch(Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
    {
        $fieldId = $term->field->identity;

        switch ($fieldId) {
            case Terms::TICKET_SLAS: $fieldValue = $ticketModel->id; break;
            default: throw new \InvalidArgumentException('Unknown field');
        }

        return $this->valueResolver->checkTermWithFieldValue($fieldValue, $term, $ticketModel, $matcherContext);
    }

    public function hasPassingSlas(array $params, Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
    {
        return $this->anySlasStatus($ticketModel, TicketSla::STATUS_OK, $params);
    }

    public function hasWarningSlas(array $params, Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
    {
        return $this->anySlasStatus($ticketModel, TicketSla::STATUS_WARNING, $params);
    }

    public function hasFailingSlas(array $params, Term $term, TicketModel $ticketModel, MatcherContext $matcherContext)
    {
        return $this->anySlasStatus($ticketModel, TicketSla::STATUS_FAIL, $params);
    }

    /**
     * @param TicketModel $ticketModel
     * @param string      $findStatus
     * @param array       $specificIds
     *
     * @return bool
     */
    private function anySlasStatus(TicketModel $ticketModel, $findStatus, array $specificIds)
    {
        foreach ($ticketModel->slasInfo as $slaInfo) {
            if ($slaInfo->status === $findStatus) {
                if (empty($specificIds) || in_array($slaInfo->sla_id, $specificIds)) {
                    return true;
                }
            }
        }

        return false;
    }
}
