<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat;

use Application\DeskPRO\Entity\TicketLog;
use Behat\Gherkin\Node\TableNode;
use DpBehat\Data\DataContext;

/**
 * Class TicketLogsContext.
 */
class TicketLogsContext extends BaseContext
{
    /**
     * @Given I reset the ":ticketId" ticket logs
     *
     * @param int $ticketId
     */
    public function iResetTicketLogs($ticketId)
    {
        $ticketId = DataContext::replace($ticketId);
        $qb       = $this->em()->createQueryBuilder();
        $qb
            ->delete()
            ->from(TicketLog::class, 'e')
            ->where('e.ticket = :ticket_id')
            ->setParameter('ticket_id', $ticketId)
        ;

        $qb->getQuery()->execute();
    }

    /**
     * @Then print the ":ticketId" ticket logs
     *
     * @param $ticketId
     */
    public function printTicketLogActions($ticketId)
    {
        $ticketId = DataContext::replace($ticketId);
        $result   = $this->getTicketLogActions($ticketId);

        $existTypes = [];
        foreach ($result as $log) {
            $existTypes[] = $log->action_type;
        }

        if (empty($existTypes)) {
            echo 'No logs created';
        } else {
            echo "Logs:\n".implode("\n", $existTypes);
        }
    }

    /**
     * @Then the ":ticketId" ticket should have ":actionType" log
     *
     * @param int    $ticketId
     * @param string $actionType
     *
     * @throws \Exception
     *
     * @return TicketLog[]
     */
    public function ticketLogsHaveAction($ticketId, $actionType)
    {
        $ticketId = DataContext::replace($ticketId);
        $result   = $this->getTicketLogActions($ticketId, [$actionType]);
        if (empty($result)) {
            throw new \Exception("No $actionType ticket log found");
        }

        return $result;
    }

    /**
     * @Then the ":ticketId" ticket should have ":actionType" log with detail ":detailName" = ":expectedValue"
     *
     * @param int    $ticketId
     * @param string $actionType
     * @param string $detailName
     * @param mixed  $expectedValue
     *
     * @throws \Exception
     */
    public function ticketLogActionHasDetail($ticketId, $actionType, $detailName, $expectedValue)
    {
        $ticketLogs = $this->ticketLogsHaveAction($ticketId, $actionType);
        foreach ($ticketLogs as $ticketLog) {
            if (isset($ticketLog->details[$detailName]) && $ticketLog->details[$detailName] == $expectedValue) {
                return;
            }
        }

        throw new \Exception("No $actionType ticket log found with $detailName = $expectedValue");
    }

    /**
     * @Then the ":ticketId" ticket should have the following logs:
     *
     * @param int       $ticketId
     * @param TableNode $actions
     *
     * @throws \Exception
     */
    public function ticketLogsHaveActions($ticketId, TableNode $actions)
    {
        $ticketId = DataContext::replace($ticketId);
        $types    = $this->getActionTypes($actions);
        $result   = $this->getTicketLogActions($ticketId, $types);

        $existTypes = [];
        foreach ($result as $log) {
            $existTypes[] = $log->action_type;
        }

        $diff = array_diff($types, $existTypes);
        if (count($diff)) {
            throw new \Exception('No ticket log found for: '.implode(',', $diff));
        }
    }

    /**
     * @Then the ":ticketId" ticket should have no the following logs:
     *
     * @param int       $ticketId
     * @param TableNode $actions
     *
     * @throws \Exception
     */
    public function ticketLogsHaveNoActions($ticketId, TableNode $actions)
    {
        $ticketId = DataContext::replace($ticketId);
        $types    = $this->getActionTypes($actions);
        $result   = $this->getTicketLogActions($ticketId, $types);

        if (!empty($result)) {
            $logs = array_map(function (TicketLog $tl) { return $tl->getActionType(); }, $result);
            throw new \Exception('Found unexpected ticket logs: '.implode(',', $logs));
        }
    }

    /**
     * @param int   $ticketId
     * @param array $types
     *
     * @return TicketLog[]
     */
    protected function getTicketLogActions($ticketId, array $types = null)
    {
        $qb = $this->em()->createQueryBuilder();
        $qb
            ->select('e')
            ->from(TicketLog::class, 'e')
            ->where('e.ticket = :ticket_id')
            ->setParameter('ticket_id', $ticketId)
        ;

        if ($types) {
            $qb->andWhere('e.action_type IN (:action_type)');
            $qb->setParameter('action_type', $types);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param TableNode $actions
     *
     * @return array
     */
    protected function getActionTypes(TableNode $actions)
    {
        $types = [];
        foreach ($actions as $action) {
            $types[] = $action['type'];
        }

        return $types;
    }
}
