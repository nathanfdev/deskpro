<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use Application\DeskPRO\Entity\ContentAbstract;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting article entity.
 *
 * Class Article
 */
class Article extends AbstractContentModel implements PersonAwareInterface, LabelAwareModelInterface, CustomDataAwareModelInterface
{
    use LabelAwareTrait, CustomDataAwareTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     */
    private $person;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\Choice(choices={"archive", "delete"})
     */
    private $end_action;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_end;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_updated;

    /**
     * @var array
     *
     * @JMS\Type("array<string>")
     *
     * @Assert\All(constraints={
     *   @Assert\NotBlank()
     * })
     */
    private $categories = [];

    /**
     * @var Attachment[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Attachment>")
     *
     * @Assert\Valid()
     */
    private $attachments = [];

    /**
     * @var Comment[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Comment>")
     *
     * @Assert\Valid()
     */
    private $comments = [];

    /**
     * @var Translation[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Translation>")
     *
     * @Assert\Valid()
     */
    private $titleTranslations = [];

    /**
     * @var Translation[]
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\Translation>")
     *
     * @Assert\Valid()
     */
    private $contentTranslations = [];

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
     * End action.
     *
     * @return string
     */
    public function getEndAction()
    {
        return $this->end_action;
    }

    /**
     * Set end action.
     *
     * @param string $end_action
     *
     * @return $this
     */
    public function setEndAction($end_action)
    {
        $this->end_action = $end_action;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        if ($this->date_end) {
            return ContentAbstract::STATUS_ARCHIVED;
        }

        return parent::getStatus();
    }

    /**
     * Returns date end of publishing.
     *
     * @return \DateTime
     */
    public function getDateEnd()
    {
        return $this->date_end;
    }

    /**
     * Set date end of publishing.
     *
     * @param \DateTime $date_end
     *
     * @return $this
     */
    public function setDateEnd(\DateTime $date_end = null)
    {
        $this->date_end = $date_end;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param \DateTime $date_updated
     *
     * @return $this
     */
    public function setDateUpdated(\DateTime $date_updated = null)
    {
        $this->date_updated = $date_updated;

        return $this;
    }

    /**
     * Returns article categories.
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     *
     * @return $this
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;

        return $this;
    }

    /**
     * Add a new category.
     *
     * @param string $category
     *
     * @return $this
     */
    public function addCategory($category)
    {
        $this->categories[] = (string) $category;

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
     * Returns article comments.
     *
     * @return Comment[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * Add an article comment.
     *
     * @param Comment $comment
     *
     * @return $this
     */
    public function addComment(Comment $comment)
    {
        $this->comments[] = $comment;

        return $this;
    }

    /**
     * Returns article title translations.
     *
     * @return Translation[]
     */
    public function getTitleTranslations()
    {
        return $this->titleTranslations;
    }

    /**
     * @param Translation[] $titleTranslations
     *
     * @return $this
     */
    public function setTitleTranslations(array $titleTranslations)
    {
        $this->titleTranslations = $titleTranslations;

        return $this;
    }

    /**
     * Add an article title translation.
     *
     * @param Translation $translation
     *
     * @return $this
     */
    public function addTitleTranslation(Translation $translation)
    {
        $this->titleTranslations[] = $translation;

        return $this;
    }

    /**
     * Returns article content translations.
     *
     * @return Translation[]
     */
    public function getContentTranslations()
    {
        return $this->contentTranslations;
    }

    /**
     * @param Translation[] $titleTranslations
     *
     * @return $this
     */
    public function setContentTranslations(array $titleTranslations)
    {
        $this->contentTranslations = $titleTranslations;

        return $this;
    }

    /**
     * Add an article content translation.
     *
     * @param Translation $translation
     *
     * @return $this
     */
    public function addContentTranslation(Translation $translation)
    {
        $this->contentTranslations[] = $translation;

        return $this;
    }
}
