<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use Application\DeskPRO;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting feedback entity.
 *
 * Class Feedback
 */
class Feedback implements PersonAwareInterface, LabelAwareModelInterface, AttachmentsAwareInterface, CustomDataAwareModelInterface, PrimaryImportModelInterface
{
    use PrimaryImportModelTrait, LabelAwareTrait, CustomDataAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    protected $content;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    protected $language;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    protected $view_count = 0;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_created;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    protected $date_published;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var int
     *
     * @JMS\Type("integer")
     */
    private $popularity = 0;

    /**
     * @var array
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Attachment>")
     *
     * @Assert\Valid()
     */
    private $attachments = [];

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *   "active",
     *   "closed",
     *   "hidden"
     * })
     */
    protected $status;

    /**
     * Feedback category.
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set feedback category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * {@inheritdoc}
     */
    public function setPerson($person)
    {
        $this->person = $person;

        return $this;
    }

    /**
     * Feedback popularity.
     *
     * @return int
     */
    public function getPopularity()
    {
        return $this->popularity;
    }

    /**
     * Set feedback popularity.
     *
     * @param int $popularity
     *
     * @return $this
     */
    public function setPopularity($popularity)
    {
        $this->popularity = (int) $popularity;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttachment(Attachment $attachment)
    {
        $this->attachments[] = $attachment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * {@inheritdoc}
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * {@inheritdoc}
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * {@inheritdoc}
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->status = $status ?: 'hidden';

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewCount($view_count)
    {
        $this->view_count = (int) $view_count;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * {@inheritdoc}
     */
    public function setDateCreated(\DateTime $date_created)
    {
        $this->date_created = $date_created;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDatePublished()
    {
        return $this->date_published;
    }

    /**
     * {@inheritdoc}
     */
    public function setDatePublished(\DateTime $date_published = null)
    {
        $this->date_published = $date_published;

        return $this;
    }
}
