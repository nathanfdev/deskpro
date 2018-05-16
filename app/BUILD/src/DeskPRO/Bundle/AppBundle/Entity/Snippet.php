<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class Snippet.
 *
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetRepository")
 * @ORM\Table(name="snippets")
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
     * @var int
     */
    protected $id;

    /**
     * Person that sent this message.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Person")
     * @ORM\JoinColumn(name="person_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var Person
     */
    protected $person;

    /**
     * @ORM\Column(type="string", length=255, name="shortcut_code")
     *
     *
     * @Assert\Regex(
     *     pattern="/^[-_a-z0-9]*$/i",
     *     message="Shortcode should only contains letters, numbers, hyphen or underscores"
     * )
     *
     * @var string
     */
    protected $shortcutCode = '';

    /**
     * Snippet title.
     *
     * @ORM\Column(type="text")
     *
     * @Assert\NotBlank()
     *
     * @var string
     */
    protected $title;

    /**
     * @ORM\Column(type="simple_array", name="types")
     *
     * @Assert\NotBlank()
     *
     * @var array
     */
    protected $types = [];

    /**
     * Flag indicates that request is dupe.
     *
     * @ORM\Column(type="boolean", nullable=false, name="is_draft")
     *
     * @var bool
     */
    protected $isDraft = false;

    /**
     * Flag indicates that content is different for different types.
     *
     * @ORM\Column(type="boolean", nullable=false, name="is_split", options={"default" : 0})
     *
     * @var bool
     */
    protected $isSplit = false;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\SnippetTranslation", mappedBy="snippet",
     *     cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @var SnippetTranslation[]|ArrayCollection
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="DeskPRO\Bundle\AppBundle\Entity\SnippetLabel", mappedBy="snippet",
     *     cascade={"persist", "remove"}, orphanRemoval=true, fetch="EAGER")
     *
     * @var SnippetLabel[]|ArrayCollection
     */
    protected $labels;

    /**
     * Flag indicates that snippet is accessible to everyone.
     *
     * @ORM\Column(type="boolean", nullable=false, name="ownership_global")
     *
     * @var bool
     */
    protected $isOwnershipGlobal = false;

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
     * @var AgentTeam[]|ArrayCollection
     */
    protected $ownershipTeams;

    /**
     * Flag indicates that snippet is visible with all departments.
     *
     * @ORM\Column(type="boolean", nullable=false, name="visible_global")
     *
     * @var bool
     */
    protected $isVisibleGlobal = false;

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
     * @var Department[]|ArrayCollection
     */
    protected $visibleDepartments;

    /**
     * @ORM\Column(name="usage_count", type="integer")
     *
     * @var int
     */
    protected $usageCount = 0;

    /**
     * @ORM\Column(name="positive_ratings", type="integer")
     *
     * @var int
     */
    protected $positiveRatings = 0;

    /**
     * @ORM\Column(name="neutral_ratings", type="integer")
     *
     * @var int
     */
    protected $neutralRatings = 0;

    /**
     * @ORM\Column(name="negative_ratings", type="integer")
     *
     * @var int
     */
    protected $negativeRatings = 0;

    /**
     * When snippet was created.
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     *
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations       = new ArrayCollection();
        $this->ownershipTeams     = new ArrayCollection();
        $this->visibleDepartments = new ArrayCollection();
        $this->setModelField('labels', new ArrayCollection());
        $this->setModelField('dateCreated', new \DateTime());
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
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return Snippet
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return array
     */
    public function getTypes()
    {
        return $this->types;
    }

    /**
     * @param array $types
     *
     * @return Snippet
     */
    public function setTypes($types)
    {
        $this->setModelField('types', $types);

        return $this;
    }

    public function addType($type)
    {
        if (!in_array($type, $this->types, true)) {
            $this->types[] = $type;
        }
        $this->setModelField('types', $this->types);

        return $this;
    }

    public function hasType($type)
    {
        return in_array($type, $this->types, true);
    }

    public function removeType($type)
    {
        if (false !== $key = array_search($type, $this->types, true)) {
            unset($this->types[$key]);
            $this->types = array_values($this->types);
        }
        $this->setModelField('types', $this->types);

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
     * @return bool
     */
    public function isSplit()
    {
        return $this->isSplit;
    }

    /**
     * @param bool $isSplit
     *
     * @return Snippet
     */
    public function setIsSplit($isSplit)
    {
        $this->setModelField('isSplit', $isSplit);

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
     * @param SnippetTranslation $translation
     *
     * @return $this
     */
    public function addTranslation(SnippetTranslation $translation)
    {
        foreach ($this->translations as $t) {
            if ($t->getLanguage()->getId() === $translation->getLanguage()->getId()
            && (!$t->getType() || $t->getType() === $translation->getType())) {
                $t->setContent($translation->getContent());
                $t->setType($translation->getType());

                return $this;
            }
        }
        if ($translation->getContent()) {
            $translation->setSnippet($this);
            $this->translations->add($translation);
        }

        return $this;
    }

    /**
     * @param SnippetTranslation $translation
     *
     * @return $this
     */
    public function removeTranslation(SnippetTranslation $translation)
    {
        $this->translations->removeElement($translation);

        return $this;
    }

    /**
     * @return SnippetLabel[]|ArrayCollection
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param SnippetLabel $label
     *
     * @return $this
     */
    public function addLabel(SnippetLabel $label)
    {
        if (!$this->hasLabel($label)) {
            $this->labels->add($label);
            $label->setSnippet($this);
            $this->setModelField('labels', $this->labels);
        }

        return $this;
    }

    /**
     * @param SnippetLabel $label
     *
     * @return bool
     */
    public function hasLabel($label)
    {
        foreach ($this->labels as $l) {
            if ($l->getLabel() === $label->getLabel()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param SnippetLabel $label
     *
     * @return $this
     */
    public function removeLabel(SnippetLabel $label)
    {
        foreach ($this->labels as $l) {
            if ($l->getLabel() === $label->getLabel()) {
                $this->labels->removeElement($l);
                $this->setModelField('labels', $this->labels);
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isOwnershipGlobal()
    {
        return $this->isOwnershipGlobal;
    }

    /**
     * @param bool $isOwnershipGlobal
     *
     * @return Snippet
     */
    public function setIsOwnershipGlobal($isOwnershipGlobal)
    {
        $this->setModelField('isOwnershipGlobal', $isOwnershipGlobal);

        return $this;
    }

    /**
     * @return AgentTeam[]
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
     * @return $this
     */
    public function clearTeams()
    {
        $this->ownershipTeams->clear();

        return $this;
    }

    /**
     * @param AgentTeam $team
     *
     * @return bool
     */
    public function hasTeam(AgentTeam $team)
    {
        return $this->ownershipTeams->contains($team);
    }

    /**
     * @return bool
     */
    public function hasTeams()
    {
        return (bool) count($this->ownershipTeams);
    }

    /**
     * @return bool
     */
    public function isVisibleGlobal()
    {
        return $this->isVisibleGlobal;
    }

    /**
     * @param bool $isVisibleGlobal
     *
     * @return Snippet
     */
    public function setIsVisibleGlobal($isVisibleGlobal)
    {
        $this->setModelField('isVisibleGlobal', $isVisibleGlobal);

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

    /**
     * @return $this
     */
    public function clearDepartments()
    {
        $this->visibleDepartments->clear();

        return $this;
    }

    /**
     * @param Department $department
     *
     * @return bool
     */
    public function hasDepartment(Department $department)
    {
        return $this->visibleDepartments->contains($department);
    }

    /**
     * @return bool
     */
    public function hasDepartments()
    {
        return (bool) count($this->visibleDepartments);
    }

    /**
     * @return int
     */
    public function getUsageCount()
    {
        return $this->usageCount;
    }

    /**
     * @param int $usageCount
     *
     * @return Snippet
     */
    public function setUsageCount($usageCount)
    {
        $this->setModelField('usageCount', $usageCount);

        return $this;
    }

    /**
     * @return int
     */
    public function getPositiveRatings()
    {
        return $this->positiveRatings;
    }

    /**
     * @param int $positiveRatings
     *
     * @return Snippet
     */
    public function setPositiveRatings($positiveRatings)
    {
        $this->setModelField('positiveRatings', $positiveRatings);

        return $this;
    }

    /**
     * @return int
     */
    public function getNeutralRatings()
    {
        return $this->neutralRatings;
    }

    /**
     * @param int $neutralRatings
     *
     * @return Snippet
     */
    public function setNeutralRatings($neutralRatings)
    {
        $this->setModelField('neutralRatings', $neutralRatings);

        return $this;
    }

    /**
     * @return int
     */
    public function getNegativeRatings()
    {
        return $this->negativeRatings;
    }

    /**
     * @param int $negativeRatings
     *
     * @return Snippet
     */
    public function setNegativeRatings($negativeRatings)
    {
        $this->setModelField('negativeRatings', $negativeRatings);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param \DateTime $dateCreated
     *
     * @return Snippet
     */
    public function setDateCreated($dateCreated)
    {
        $this->setModelField('dateCreated', $dateCreated);

        return $this;
    }
}
