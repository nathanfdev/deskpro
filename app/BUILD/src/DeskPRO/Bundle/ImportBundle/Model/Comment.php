<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Exporting article comment entity.
 *
 * Class ArticleComment
 */
class Comment implements OidAwareModelInterface
{
    use OidRequiredAwareModelTrait;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $content;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     * @Assert\Choice(choices={
     *   "visible",
     *   "deleted",
     *   "agent"
     * })
     */
    private $status;

    /**
     * @var string
     *
     * @JMS\Type("string")
     *
     * @Assert\NotBlank()
     */
    private $person;

    /**
     * @var bool
     *
     * @JMS\Type("boolean")
     */
    private $is_reviewed = false;

    /**
     * @var \DateTime
     *
     * @JMS\Type("DateTime")
     */
    private $date_created;

    /**
     * Returns comment content.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set comment content.
     *
     * @param string $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Returns status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set comment status.
     *
     * @param string $status
     *
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
     * Is comment reviewed.
     *
     * @return bool
     */
    public function isReviewed()
    {
        return $this->is_reviewed;
    }

    /**
     * Set as reviewed.
     *
     * @param bool $is_reviewed
     *
     * @return $this
     */
    public function setAsReviewed($is_reviewed)
    {
        $this->is_reviewed = $is_reviewed;

        return $this;
    }

    /**
     * Returns date created.
     *
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * Set date created.
     *
     * @param \DateTime $date_created
     *
     * @return $this
     */
    public function setDateCreated(\DateTime $date_created = null)
    {
        $this->date_created = $date_created;

        return $this;
    }
}
