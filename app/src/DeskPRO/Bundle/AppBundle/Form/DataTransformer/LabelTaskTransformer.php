<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use Application\DeskPRO\ORM\EntityManager;
use DeskPRO\Bundle\AppBundle\Entity\LabelTask;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use Symfony\Component\Form\DataTransformerInterface;

class LabelTaskTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Task
     */
    private $task;

    /**
     * Constructor.
     *
     * @param EntityManager $entityManager
     * @param Task          $task
     */
    public function __construct(EntityManager $entityManager, Task $task = null)
    {
        $this->entityManager = $entityManager;
        $this->task          = $task;
    }

    /**
     * Transform a label object to a string.
     *
     * @param mixed $labelObject
     *
     * @return string
     */
    public function transform($labelObject)
    {
        if (!is_null($labelObject) && ($labelObject instanceof LabelTask)) {
            return $labelObject->getLabel();
        }

        return '';
    }

    /**
     * Transform a label string to a label object (requires the task).
     *
     * @param string $label
     *
     * @return LabelTask|null|object
     */
    public function reverseTransform($label)
    {
        if (!$this->task) {
            return;
        }

        if ($this->task->getLabels()->contains($label)) {
            return $label;
        }

        $labelObject = $this->entityManager->getRepository('App:LabelTask')
            ->findOneBy(array('label' => $label, 'task' => $this->task));

        if (empty($labelObject)) {
            $labelObject = new LabelTask();
            $labelObject->setLabel($label);
            $labelObject->setTask($this->task);
        }

        return $labelObject;
    }
}
