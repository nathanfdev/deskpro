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

/**
 * DeskPRO.
 *
 * @category Entities
 */
namespace DeskPRO\Bundle\AppBundle\Entity\TaskLinkedItem;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Entity\NotifyPropertyChangedTrait;
use DeskPRO\Bundle\AppBundle\Entity\Task;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @JMS\ExclusionPolicy("all")
 *
 * @ORM\Entity
 * @ORM\Table(name="task_links", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="ticket_unique", columns={"task_id", "ticket_id"}),
 *      @ORM\UniqueConstraint(name="chat_unique", columns={"task_id", "chat_id"}),
 *      @ORM\UniqueConstraint(name="article_unique", columns={"task_id", "article_id"})
 *  })
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string", length=30)
 * @ORM\DiscriminatorMap({
 *     "item" = "TaskLinkedItem",
 *     "ticket" = "TaskLinkedTicket",
 *     "article" = "TaskLinkedArticle",
 *     "chat" = "TaskLinkedChat"
 * })
 */
class TaskLinkedItem implements EntityInterface
{

    /**
     * The unique ID.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @var int
     */
    protected $id = null;

    /**
     * Related task.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Task>")
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", nullable=false)
     * @Assert\NotNull()
     * @Assert\Valid()
     *
     * @var Task
     */
    protected $task;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     *
     * @return $this
     */
    public function setTask(Task $task)
    {
        $this->task = $task;

        return $this;
    }
}
