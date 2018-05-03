<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Entity\Person;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SnippetChangeLog.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity()
 * @ORM\Table(name="snippet_changelog")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class SnippetChangeLog implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

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
     * When action was done.
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @Assert\NotNull()
     *
     * @var \DateTime
     */
    protected $dateCreated;

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
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Snippet")
     * @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Snippet>")
     *
     * @var Snippet
     */
    protected $snippet;

    /**
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    protected $language;

    /**
     * Snippet content.
     *
     * @ORM\Column(type="text")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $content;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups({"list"})
     *
     * @var string
     */
    protected $type;

    /**
     * @ORM\ManyToMany(targetEntity="Application\DeskPRO\Entity\Blob")
     * @ORM\JoinTable(
     *     name="snippet_changelog_blob",
     *     joinColumns={
     *          @ORM\JoinColumn(name="snippet_changelog_id", referencedColumnName="id", onDelete="CASCADE")
     *     },
     *     inverseJoinColumns={
     *          @ORM\JoinColumn(name="blob_id", referencedColumnName="id", onDelete="CASCADE")
     *     }
     * )
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\Blob>>")
     *
     * @var Blob[]|ArrayCollection
     */
    protected $blobs;

    public function __construct()
    {
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
     * @return SnippetChangeLog
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
     * @return SnippetChangeLog
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return Snippet
     */
    public function getSnippet()
    {
        return $this->snippet;
    }

    /**
     * @param Snippet $snippet
     *
     * @return SnippetChangeLog
     */
    public function setSnippet(Snippet $snippet = null)
    {
        $this->setModelField('snippet', $snippet);

        return $this;
    }

    /**
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param Language $language
     *
     * @return SnippetChangeLog
     */
    public function setLanguage(Language $language = null)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param string $content
     *
     * @return SnippetChangeLog
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return SnippetChangeLog
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
    }

    /**
     * @return Blob[]|ArrayCollection
     */
    public function getBlobs()
    {
        return $this->blobs;
    }

    /**
     * @param Blob[]|ArrayCollection $blobs
     *
     * @return SnippetChangeLog
     */
    public function setBlobs($blobs)
    {
        $this->setModelField('blobs', $blobs);

        return $this;
    }
}
