<?php

/**
 * DeskPRO.
 *
 * @category People
 */

namespace Application\DeskPRO\People\ActivityLogger;

use Application\DeskPRO\DBAL\Connection;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\People\ActivityLogger\ActionType\ActionTypeAbstract;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Class ActivityLogger.
 */
class ActivityLogger
{
    /**
     * @var Connection
     */
    protected $db;

    /**
     * @var PersonActivity[]
     */
    protected $pending = [];

    /**
     * ActivityLogger constructor.
     *
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->db = $em->getConnection();

        $me = $this;
        \DpShutdown::add(function () use ($me) {
            $me->flush();
        });
    }

    /**
     * Save any action details.
     *
     * @param \Application\DeskPRO\Entity\Person $person
     * @param string                             $action_type
     * @param array                              $details
     *
     * @return \Application\DeskPRO\Entity\PersonActivity|array
     */
    private function createActionDetails(Person $person, $action_type, array $details)
    {
        $activity = new PersonActivity();
        $activity->setPerson($person);
        $activity['action_type'] = $action_type;
        $activity['details']     = $details;

        return $activity;
    }

    /**
     * @param ActionTypeAbstract $action
     */
    public function saveAction(ActionTypeAbstract $action)
    {
        $action_type = Util::getBaseClassname($action);
        $action_type = Strings::camelCaseToUnderscore($action_type);

        $details = $action->getDetails();
        $act     = $this->createActionDetails($action->getPersonContext(), $action_type, $details);

        $this->pending[] = $act;
    }

    /**
     * Flush log entries to db.
     */
    public function flush()
    {
        $pending       = $this->pending;
        $this->pending = [];

        if (!$pending) {
            return;
        }

        // get all affected people to check if they still exist in the db
        // and we don't get a FK error

        $ids = [];
        foreach ($pending as $activity) {
            $ids[$activity->getPersonId()] = true;
        }

        $ids = $this->db->fetchAllCol(
            'SELECT id FROM people WHERE id IN (:ids)',
            [
                'ids' => array_keys($ids),
            ],
            [
                'ids' => Connection::PARAM_INT_ARRAY,
            ]
        );

        // using plain sql here instead of doctrine
        // to avoid any need for EM to have any related entities

        $batch = [];
        foreach ($pending as $a) {
            if (!in_array($a->getPersonId(), $ids)) {
                continue;
            }

            $batch[] = $a->toDbArray();
        }

        if ($batch) {
            $this->db->batchInsert('person_activity', $batch, true);
        }
    }
}
