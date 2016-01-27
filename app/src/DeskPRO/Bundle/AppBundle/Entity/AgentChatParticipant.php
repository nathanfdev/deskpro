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

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class AgentChatParticipant.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\AgentChatParticipant")
 * @ORM\Table(name="agent_chat_participant")
 * @ORM\ChangeTrackingPolicy("DEFERRED_IMPLICIT")
 * @ORM\InheritanceType("NONE")
 */
class AgentChatParticipant implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * @var int
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var AgentChat
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\AgentChat", inversedBy="participants")
     * @ORM\JoinColumn(name="agent_chat_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $chat;

    /**
     * @var Person|null
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $person;

    /**
     * @var AgentTeam|null
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $team;

    /**
     * @var Department
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Department")
     * @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $department;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @return AgentChat
     */
    public function getChat()
    {
        return $this->chat;
    }
    /**
     * @param AgentChat $chat
     *
     * @return $this
     */
    public function setChat(AgentChat $chat)
    {
        $this->chat = $chat;

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
     * @return Person[]
     */
    public function getPersonList()
    {
        if ($this->person) {
            return array($this->getPerson());
        } elseif ($this->team) {
            return $this->team->getPersonList();
        } elseif ($this->department) {
            return $this->department->getPersonList();
        } else {
            return;
        }
    }
    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        $this->person     = $person;
        $this->department = null;
        $this->team       = null;

        return $this;
    }
    /**
     * @return AgentTeam
     */
    public function getTeam()
    {
        return $this->team;
    }
    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function setTeam(AgentTeam $team)
    {
        $this->team       = $team;
        $this->department = null;
        $this->person     = null;

        return $this;
    }
    /**
     * @return Department
     */
    public function getDepartment()
    {
        return $this->department;
    }
    /**
     * @param Department $department
     *
     * @return $this
     */
    public function setDepartment(Department $department)
    {
        $this->department = $department;
        $this->person     = null;
        $this->team       = null;

        return $this;
    }

    /**
     * @return int
     */
    public function getPersonId()
    {
        return ($this->getPerson()) ? $this->getPerson()->getId() : null;
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return ($this->getTeam()) ? $this->getTeam()->getId() : null;
    }

    /**
     * @return int
     */
    public function getDepartmentId()
    {
        return ($this->getDepartment()) ? $this->getDepartment()->getId() : null;
    }
}
