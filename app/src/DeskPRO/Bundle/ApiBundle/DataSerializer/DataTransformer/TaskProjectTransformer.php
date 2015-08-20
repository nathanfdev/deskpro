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
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use DeskPRO\Bundle\AppBundle\Entity\Task;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Entity\ProjectMember;
use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use Doctrine\Common\Collections\ArrayCollection;

class TaskProjectTransformer extends AbstractDataSerializerTransformer
{
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return ['id', 'title'];
    }

    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        $data = $transformation_request->getDataToBeTransformed();

        /** @var TaskList[]|ArrayCollection $listsData */
        $listsData = $data->getLists();

        $lists = [];

        if (!empty($listsData)) {
            foreach ($listsData as $list) {
                if (!empty($list->getDisplayOrder())) {
                    $lists['order:' . $list->getDisplayOrder()] = $list->getTitle();
                } else {
                    $lists[] = $list->getTitle();
                }
            }

            ksort($lists, SORT_STRING);
            $lists = array_values($lists);
        }

        /** @var ProjectMember[]|ArrayCollection $members */
        $members = $data->getMembers();

        $groupedMembers = [
            'departments' => [],
            'teams' => [],
            'agents' => [],
        ];

        if (!empty($members)) {
            foreach ($members as $member) {
                if (!empty($member->getDepartment())) {
                    $groupedMembers['departments'][] = $member->getDepartment()->getId();
                } else if (!empty($member->getTeam())) {
                    $groupedMembers['teams'][] = $member->getTeam()->getId();
                } else {
                    $groupedMembers['agents'][] = $member->getPerson()->getId();
                }
            }
        }

        /** @var Task[]|ArrayCollection $tasks */
        $tasks = $data->getTasks();

        $remaining = 0;
        foreach ($tasks as $task) {
            if (!$task->isDone()) {
                $remaining++;
            }
        }

        return [
            'departments' => $groupedMembers['departments'],
            'teams' => $groupedMembers['teams'],
            'agents' => $groupedMembers['agents'],
            'remaining' => $remaining,
            'lists' => $lists,
        ];
    }
}
