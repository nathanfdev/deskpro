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
 *
 * @category Entities
 */
namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="custom_task_data")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class CustomDataTask implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Assert\NotNull()
     */
    protected $id = null;

    /**
     * @var Task
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $task;

    /**
     * @var CustomDefTask
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\CustomDefTask")
     * @ORM\JoinColumn(name="field_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $field;

    /**
     * @var CustomDefTask
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\CustomDefTask")
     * @ORM\JoinColumn(name="root_field_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $root_field;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return CustomDefTask
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * @param CustomDefTask $field
     */
    public function setField($field)
    {
        $this->field = $field;
        $this->setModelField('field', $field);
    }

    /**
     * @return CustomDefTask
     */
    public function getRootField()
    {
        return $this->root_field;
    }

    /**
     * @param CustomDefTask $root_field
     */
    public function setRootField($root_field)
    {
        $this->root_field = $root_field;
        $this->setModelField('root_field', $root_field);
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @param Task $task
     */
    public function setTask(Task $task)
    {
        $this->task = $task;
        $this->setModelField('task', $task);
    }
}
