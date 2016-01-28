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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Hateoas\Configuration\Annotation as Hateoas;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_attachments")
 *
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route("api_task_attachments_get", parameters={"id" = "expr(object.getId())"})
 * )
 */
class TaskAttachment implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id = null;

    /**
     * @var Task
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id", onDelete="CASCADE")
     * @Assert\NotNull()
     * @Assert\Valid()
     */
    protected $task;

    /**
     * @var TaskComment
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\TaskComment")
     * @ORM\JoinColumn(name="task_comment_id", referencedColumnName="id", onDelete="CASCADE", nullable=true)
     */
    protected $comment;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * @var Blob
     * @ORM\OneToOne(targetEntity="Application\DeskPRO\Entity\Blob")
     * @ORM\JoinColumn(name="blob_id", referencedColumnName="id")
     */
    protected $blob;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date_created;

    /**
     * Constructor.
     *
     * @param Person $person
     */
    public function __construct(Person $person)
    {
        $this->setDateCreated(new \DateTime());
        $this->setPerson($person);
    }

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
     */
    public function setTask(Task $task)
    {
        $this->setModelField('task', $task);
    }

    /**
     * @return TaskComment
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param TaskComment $comment
     */
    public function setComment(TaskComment $comment)
    {
        $this->setModelField('comment', $comment);
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);
    }

    /**
     * @return Blob
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param Blob $blob
     */
    public function setBlob(Blob $blob)
    {
        $this->setModelField('blob', $blob);
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param \DateTime $date_created
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->setModelField('date_created', $date_created);
    }
}
