<?php

/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 * @category Entities
 */

namespace DeskPRO\Bundle\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use DeskPRO\Bundle\AppBundle\Doctrine\NotifyPropertyChangeEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="task_log")
 */
class TaskLog extends NotifyPropertyChangeEntity
{
    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     * @Assert\NotNull()
     */
    protected $id = null;

    /**
     * @var Person
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id")
     */
    protected $person;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    protected $action_type;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $id_object;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $id_before;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $id_after;

    /**
     * @var array
     */
    protected $details = array();

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    protected $date_created;

    /**
     * @var TaskLog
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\TaskLog")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var Task
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Task")
     * @ORM\JoinColumn(name="task_id", referencedColumnName="id")
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
        $this->person = $person;
        $this->team = null;
        $this->department = null;
        $this->setModelField('person', $person);
    }
}