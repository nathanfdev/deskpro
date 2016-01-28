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

namespace Application\ImportBundle\Entity;

use Application\DeskPRO\Entity as DeskPROEntity;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Exporting article comment entity.
 *
 * Class ArticleComment
 */
final class ArticleComment extends AbstractEntity
{
    /**
     * @var string
     */
    private $content;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $person_email;

    /**
     * @var bool
     */
    private $is_reviewed = false;

    /**
     * @var \DateTime
     */
    private $date_created;

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ARTICLE_COMMENT;
    }

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
     * Returns a list of available statuses.
     *
     * @return array
     */
    public static function getValidStatuses()
    {
        return array(
            DeskPROEntity\ArticleComment::STATUS_VISIBLE,
            DeskPROEntity\ArticleComment::STATUS_DELETED,
            DeskPROEntity\ArticleComment::STATUS_AGENT,
        );
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
     * @return bool
     */
    public function isStatusValid()
    {
        return in_array($this->status, self::getValidStatuses(), true);
    }

    /**
     * {@inheritdoc}
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * {@inheritdoc}
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;

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

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'          => $this->oid,
            'person_email' => $this->person_email,
            'content'      => $this->content,
            'status'       => $this->status,
            'is_reviewed'  => $this->is_reviewed,
            'date_created' => $this->date_created ? $this->date_created->format('Y-m-d H:i:s') : null,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        parent::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('content', new Constraints\NotBlank())
            ->addPropertyConstraint('person_email', new Constraints\NotBlank())
            ->addPropertyConstraint('status', new Constraints\NotBlank())

            ->addGetterConstraint('statusValid', new Constraints\True(array(
                'message' => sprintf('Value is not valid, use one of (%s): ', implode(', ', self::getValidStatuses())),
            )))
        ;
    }
}
