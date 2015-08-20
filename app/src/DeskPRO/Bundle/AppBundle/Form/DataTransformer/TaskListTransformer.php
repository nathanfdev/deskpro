<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\TaskList;
use DeskPRO\Bundle\AppBundle\Entity\TaskProject;
use Symfony\Component\Form\DataTransformerInterface;

class TaskListTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var TaskProject
     */
    private $project;

    /**
     * Constructor
     * @param EntityManager $entityManager
     * @param TaskProject $project
     */
    public function __construct(EntityManager $entityManager, TaskProject $project)
    {
        $this->entityManager = $entityManager;
        $this->project = $project;
    }

    /**
     * Transform a list object to a string
     * @param mixed $listObject
     * @return string
     */
    public function transform($listObject)
    {
        if (!is_null($listObject) && ($listObject instanceof TaskList)) {
            return $listObject->getTitle();
        }

        return '';
    }

    /**
     * Transform a list title into a list object (requires the project)
     * @param string $list
     * @return TaskList|null|object
     */
    public function reverseTransform($list)
    {
        $listObject = $this->entityManager->getRepository('App:TaskList')
            ->findOneBy(array('title' => $list, 'project' => $this->project));

        if (empty($listObject)) {
            $listObject = new TaskList();
            $listObject->setTitle($list);
            $listObject->setProject($this->project);
        }

        return $listObject;
    }
}
