<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People\PersonMerge;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\People\ActivityLogger;
use Doctrine\ORM\EntityManager;

/**
 * Handles Person Merge revert.
 */
class PersonMergeUndo
{
    /**
     * @var MergeBackup
     */
    protected $mergeBackup;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var ActivityLogger\ActivityLogger
     */
    protected $activityLogger;

    /**
     * @param EntityManager                 $em
     * @param MergeBackup                   $mergeBackup
     * @param ActivityLogger\ActivityLogger $activityLogger
     */
    public function __construct(EntityManager $em, MergeBackup $mergeBackup, ActivityLogger\ActivityLogger $activityLogger)
    {
        $this->em             = $em;
        $this->mergeBackup    = $mergeBackup;
        $this->activityLogger = $activityLogger;
    }

    /**
     * @param PersonActivity $mergedActivity
     *
     * @throws \InvalidArgumentException
     * @throws \Exception
     *
     * @return Person
     */
    public function undo(PersonActivity $mergedActivity)
    {
        $details = $mergedActivity->getDetails();

        $backupId = isset($details['datastore_merge_backup_id']) ? $details['datastore_merge_backup_id'] : false;
        $isUndone = isset($details['undone']) ? true : false;

        if (!$backupId || $isUndone) {
            throw new \InvalidArgumentException('[PersonMergeUndo] Merge already undone or can\'t be reverted');
        }

        $person      = $mergedActivity->getPerson();
        $otherPerson = new Person();

        $this->em->beginTransaction();

        try {
            $this->mergeBackup->restore($person, $otherPerson, $backupId);
            $this->mergeBackup->remove($backupId);

            $this->markMergedActivityAsUndone($mergedActivity);
            $this->createMergeUndoneActivity($person, $otherPerson);

            $this->em->flush();
            $this->em->commit();
        } catch (\Exception $e) {
            $this->em->rollback();

            throw $e;
        }

        return $otherPerson;
    }

    /**
     * @param PersonActivity $mergedActivity
     */
    protected function markMergedActivityAsUndone(PersonActivity $mergedActivity)
    {
        $actDetails = $mergedActivity['details'];
        unset($actDetails['datastore_merge_backup_id']);
        $actDetails                = array_merge($actDetails, ['undone' => true]);
        $mergedActivity['details'] = $actDetails;
    }

    /**
     * @param Person $person
     * @param Person $otherPerson
     */
    protected function createMergeUndoneActivity(Person $person, Person $otherPerson)
    {
        $action = new ActivityLogger\ActionType\MergeUndone($person, $otherPerson);
        $this->activityLogger->saveAction($action);

        // call flush directly to execute it inside of transaction
        $this->activityLogger->flush();
    }
}
