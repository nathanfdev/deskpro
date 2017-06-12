<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Snippet.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetRepository")
 * @ORM\Table(name="snippet")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class Snippet implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    const TYPE_TICKET = 'ticket';
    const TYPE_CHAT   = 'chat';

    /**
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Person that sent this message.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(type="string", length=255, name="shortcut_code")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $shortcutCode = '';

    /**
     * @ORM\Column(type="text", name="types")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $types = '';

    /**
     * Flag indicates that request is dupe.
     *
     * @ORM\Column(type="boolean", nullable=false, name="is_draft")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"list"})
     *
     * @var bool
     */
    protected $isDraft = false;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation", mappedBy="snippet",
     *     cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation>>")
     *
     * @var SnippetTranslation[]|ArrayCollection
     */
    protected $translations;

    /**
     * Flag indicates that request is dupe.
     *
     * @ORM\Column(type="boolean", nullable=false, name="ownership_global")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"list"})
     *
     * @var bool
     */
    protected $ownershipGlobal = false;

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\AgentTeam")
     * @ORM\JoinTable(
     *     name="snippet_ownership_teams",
     *     joinColumns={
     *          @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="agent_team_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\AgentTeam>>")
     *
     * @var AgentTeam[]|ArrayCollection
     */
    protected $ownershipTeams;

    /**
     * Flag indicates that request is dupe.
     *
     * @ORM\Column(type="boolean", nullable=false, name="visible_global")
     *
     * @JMS\Expose()
     * @JMS\Type("boolean")
     * @JMS\Groups({"list"})
     *
     * @var bool
     */
    protected $visibleGlobal = false;

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Department")
     * @ORM\JoinTable(
     *     name="snippet_visible_departments",
     *     joinColumns={
     *          @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="department_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Department>>")
     *
     * @var Department[]|ArrayCollection
     */
    protected $visibleDepartments;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations       = new ArrayCollection();
        $this->ownershipTeams     = new ArrayCollection();
        $this->visibleDepartments = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return Snippet
     */
    public function setId($id)
    {
        $this->setModelField('id', $id);

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
     *
     * @return Snippet
     */
    public function setPerson($person)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortcutCode()
    {
        return $this->shortcutCode;
    }

    /**
     * @param mixed $shortcutCode
     *
     * @return Snippet
     */
    public function setShortcutCode($shortcutCode)
    {
        $this->setModelField('shortcutCode', $shortcutCode);

        return $this;
    }

    /**
     * @return string
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param string $types
     *
     * @return Snippet
     */
    public function setTypes($types)
    {
        $this->setModelField('types', $types);

        return $this;
    }

    /**
     * @return bool
     */
    public function isDraft()
    {
        return $this->isDraft;
    }

    /**
     * @param bool $isDraft
     *
     * @return Snippet
     */
    public function setIsDraft($isDraft)
    {
        $this->setModelField('isDraft', $isDraft);

        return $this;
    }

    /**
     * @return SnippetTranslation[]|ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return bool
     */
    public function isOwnershipGlobal()
    {
        return $this->ownershipGlobal;
    }

    /**
     * @param bool $ownershipGlobal
     *
     * @return Snippet
     */
    public function setOwnershipGlobal($ownershipGlobal)
    {
        $this->setModelField('ownershipGlobal', $ownershipGlobal);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwnershipTeams()
    {
        return $this->ownershipTeams;
    }

    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function addTeam(AgentTeam $team)
    {
        if (!$this->ownershipTeams->contains($team)) {
            $this->ownershipTeams->add($team);
        }

        return $this;
    }

    /**
     * @param AgentTeam $team
     *
     * @return $this
     */
    public function removeTeam(AgentTeam $team)
    {
        $this->ownershipTeams->removeElement($team);

        return $this;
    }

    /**
     * @return bool
     */
    public function isVisibleGlobal()
    {
        return $this->visibleGlobal;
    }

    /**
     * @param bool $visibleGlobal
     *
     * @return Snippet
     */
    public function setVisibleGlobal($visibleGlobal)
    {
        $this->setModelField('visibleGlobal', $visibleGlobal);

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function addDepartment(Department $department)
    {
        if (!$this->visibleDepartments->contains($department)) {
            $this->visibleDepartments->add($department);
        }

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return $this
     */
    public function removeDepartment(Department $department)
    {
        $this->visibleDepartments->removeElement($department);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVisibleDepartments()
    {
        return $this->visibleDepartments;
    }
}
