<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Person;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class DirectMessageBlock
 *
 * @package DeskPRO\Bundle\AppBundle\Entity
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\DirectMessageBlock")
 * @ORM\Table(name="direct_message_blocks")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 */
class DirectMessageBlock
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     */
    protected $id;

    /**
     * @var DirectMessageThread
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\DirectMessageThread")
     * @ORM\JoinColumn(name="during_thread_id", onDelete="SET NULL", nullable=true)
     */
    protected $duringThread;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", onDelete="SET NULL", nullable=true)
     */
    protected $person;

    /**
     * @var Person
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="by_person_id", onDelete="SET NULL", nullable=true)
     */
    protected $byPerson;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime")
     */
    protected $dateCreated;

    /**
     * DirectMessageBlock constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $this->setModelField('dateCreated', new \DateTime());
    }

    /**
     * @param EntityManagerInterface $em
     * @param DirectMessageThread $thread
     * @param Person $byPerson
     * @return DirectMessageBlock
     * @throws \Exception
     */
    public static function createFromThread(EntityManagerInterface $em, DirectMessageThread $thread, Person $byPerson)
    {
        /** @var Person $person */
        $person = $em->getReference(
            Person::class,
            current(array_filter($thread->getParticipantIds(), function ($id) use ($byPerson) {
                return $byPerson->getId() != $id;
            }))
        );

        return (new self)
            ->setDuringThread($thread)
            ->setByPerson($byPerson)
            ->setPerson($person)
        ;
    }

    /**
     * @return DirectMessageThread
     */
    public function getDuringThread()
    {
        return $this->duringThread;
    }

    /**
     * @param DirectMessageThread $duringThread
     * @return DirectMessageBlock
     */
    public function setDuringThread(DirectMessageThread $duringThread)
    {
        $this->setModelField('duringThread', $duringThread);

        return $this;
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
     * @return DirectMessageBlock
     */
    public function setPerson(Person $person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Person
     */
    public function getByPerson()
    {
        return $this->byPerson;
    }

    /**
     * @param Person $byPerson
     * @return DirectMessageBlock
     */
    public function setByPerson(Person $byPerson)
    {
        $this->setModelField('byPerson', $byPerson);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }
}
