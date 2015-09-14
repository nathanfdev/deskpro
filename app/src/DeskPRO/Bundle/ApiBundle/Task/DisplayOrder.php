<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * DeskPRO.
 */

namespace DeskPRO\Bundle\ApiBundle\Task;

use DeskPRO\Bundle\AppBundle\Entity\Task;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\QueryBuilder;

class DisplayOrder
{
    /**
     * Reposition a task and commit the changes to the database
     * @param ObjectManager $entityManager
     * @param Task $task
     * @param $newPosition
     * @return array
     */
    public function reposition(ObjectManager $entityManager, Task $task, $newPosition)
    {
        $relatedTasks = $this->getRelatedTasks($entityManager, $task, $newPosition);

        // We need to do this as $newPosition is actually the ID of the parent card.
        // If we're moving up the parent card stays put, so we need to add one
        // Otherwise, we're replacing the parent card, so we can use it's old
        // display_order value.
        if ($newPosition > $task->getDisplayOrder()) {
            $newPosition++;
        }

        // Remove the task from the list of related tasks
        /** @var Task $related */
        $relatedTasks = array_values(array_filter($relatedTasks, function($related) use ($task) {
            return $related->getId() !== $task->getId();
        }));

        // Re-insert the task in the new place
        foreach($relatedTasks as $key => $related) {
            if ($related->getDisplayOrder() === $newPosition) {
                array_splice($relatedTasks, $key, 0, [$task]);
                // Might as well break here so we don't try and add the position in again
                break;
            }
        }

        // Get the first position to set
        $updatedPosition = min($newPosition, $task->getDisplayOrder());

        // Loop through, updating the display orders
        foreach($relatedTasks as $related) {
            $related->setDisplayOrder($updatedPosition);
            $entityManager->persist($related);
            $updatedPosition++;
        }

        // Commit the changes
        $entityManager->flush();

        return $relatedTasks;
    }

    /**
     * @param ObjectManager $entityManager
     * @param Task $task
     * @param $newPosition
     * @return array|void
     */
    protected function getRelatedTasks(ObjectManager $entityManager, Task $task, $newPosition)
    {
        $oldPosition = $task->getDisplayOrder();

        if ($oldPosition === $newPosition) {
            return [];
        }

        $list = $task->getList();

        // Get tasks between positions
        /** @var QueryBuilder $query */
        $query = $entityManager->createQueryBuilder();

        $query = $query->select('t')->from('App:Task', 't');

        if ($oldPosition > $newPosition) {
            $query = $query->andWhere('t.display_order <= :oldPosition')->andWhere('t.display_order >= :newPosition');
        } else {
            $query = $query->andWhere('t.display_order >= :oldPosition')->andWhere('t.display_order <= :newPosition');
        }

        $query = $query->andWhere('t.list = :listId')->orderBy('t.display_order', 'ASC');

        $query = $query->setParameter('oldPosition', $oldPosition);
        $query = $query->setParameter('newPosition', $newPosition);
        $query = $query->setParameter('listId', $list);

        $results = $query->getQuery();

        return $results->getResult();
    }
}
