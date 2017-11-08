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

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\NotifyPropertyChanged;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SnippetTranslation.
 *
 * @JMS\ExclusionPolicy("all")
 * @ORM\Entity(repositoryClass="DeskPRO\Bundle\AppBundle\Entity\Repository\SnippetTranslationRepository")
 * @ORM\Table(name="snippet_translations")
 * @ORM\ChangeTrackingPolicy("NOTIFY")
 * @ORM\InheritanceType("NONE")
 */
class SnippetTranslation implements EntityInterface, NotifyPropertyChanged
{
    use NotifyPropertyChangedTrait;

    /**
     * The unique ID.
     *
     * @ORM\Id()
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id;

    /**
     * Snippet translated.
     *
     * @ORM\ManyToOne(targetEntity="DeskPRO\Bundle\AppBundle\Entity\Snippet", inversedBy="translations")
     * @ORM\JoinColumn(name="snippet_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<DeskPRO\Bundle\AppBundle\Entity\Snippet>")
     *
     * @Assert\NotBlank()
     *
     * @var Snippet
     */
    protected $snippet;

    /**
     * Snippet translated.
     *
     * @ORM\ManyToOne(targetEntity="Application\DeskPRO\Entity\Language")
     * @ORM\JoinColumn(name="language_id", referencedColumnName="id", onDelete="CASCADE")
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @Assert\NotBlank()
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
     * Snippet title.
     *
     * @ORM\Column(type="text", nullable=true)
     *
     * @var string
     */
    protected $title;

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
     *     name="snippet_translation_blob",
     *     joinColumns={
     *          @ORM\JoinColumn(name="snippet_translation_id", referencedColumnName="id", onDelete="CASCADE")
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

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->blobs = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
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
     * @return SnippetTranslation
     */
    public function setSnippet(Snippet $snippet)
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
     * @return SnippetTranslation
     */
    public function setLanguage(Language $language)
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
     * @return SnippetTranslation
     */
    public function setContent($content)
    {
        $this->setModelField('content', $content);

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
     * @return SnippetTranslation
     */
    public function setTitle($title)
    {
        $this->setModelField('title', $title);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return SnippetTranslation
     */
    public function setType($type)
    {
        $this->setModelField('type', $type);

        return $this;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function addDepartment(Blob $blob)
    {
        if (!$this->blobs->contains($blob)) {
            $this->blobs->add($blob);
        }

        return $this;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function removeDepartment(Blob $blob)
    {
        $this->blobs->removeElement($blob);

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
     * @param Blob $blob
     *
     * @return $this
     */
    public function addBlob(Blob $blob)
    {
        $this->blobs->add($blob);

        return $this;
    }

    /**
     * @param Blob $blob
     *
     * @return $this
     */
    public function removeBlob(Blob $blob)
    {
        $this->blobs->removeElement($blob);

        return $this;
    }
}
