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

use Application\DeskPRO\Entity\ContentAbstract;
use DateTime;
use Orb\Util\Strings;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Basic properties on content.
 *
 * Class AbstractContentEntity
 */
abstract class AbstractContentEntity extends AbstractEntity implements ContentAwareInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $content;

    /**
     * @var string
     */
    protected $slug;

    /**
     * @var string
     */
    protected $language;

    /**
     * @var string
     */
    protected $status;

    /**
     * @var int
     */
    protected $view_count = 0;

    /**
     * @var int
     */
    protected $total_rating = 0;

    /**
     * @var int
     */
    protected $num_comments = 0;

    /**
     * @var int
     */
    protected $num_ratings = 0;

    /**
     * @var DateTime
     */
    protected $date_created;

    /**
     * @var DateTime
     */
    protected $date_published;

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
    public function getSlug()
    {
        if ($this->slug) {
            return $this->slug;
        }

        return Strings::slugifyTitle($this->title);
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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
        if ($this->date_published) {
            return ContentAbstract::STATUS_PUBLISHED;
        }

        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->status = $status ?: 'hidden.'.ContentAbstract::HIDDEN_STATUS_UNPUBLISHED;

        return $this;
    }

    /**
     * Returns a list of available statuses.
     *
     * @return array
     */
    public static function getValidStatuses()
    {
        $hidden_prefix = 'hidden.';

        return array(
            ContentAbstract::STATUS_PUBLISHED,
            ContentAbstract::STATUS_ARCHIVED,
            ContentAbstract::STATUS_HIDDEN,

            $hidden_prefix.ContentAbstract::HIDDEN_STATUS_UNPUBLISHED,
            $hidden_prefix.ContentAbstract::HIDDEN_STATUS_DELETED,
            $hidden_prefix.ContentAbstract::HIDDEN_STATUS_SPAM,
            $hidden_prefix.ContentAbstract::HIDDEN_STATUS_DRAFT
        );
    }

    /**
     * Checks if status is valid.
     *
     * @return bool
     */
    public function isStatusValid()
    {
        return in_array($this->status, static::getValidStatuses(), true);
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
    public function getTotalRating()
    {
        return $this->total_rating;
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalRating($total_rating)
    {
        $this->total_rating = (int) $total_rating;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumComments()
    {
        return $this->num_comments;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumComments($num_comments)
    {
        $this->num_comments = (int) $num_comments;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getNumRatings()
    {
        return $this->num_ratings;
    }

    /**
     * {@inheritdoc}
     */
    public function setNumRatings($num_ratings)
    {
        $this->num_ratings = (int) $num_ratings;

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
    public function setDateCreated(DateTime $date_created)
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
    public function setDatePublished(DateTime $date_published = null)
    {
        $this->date_published = $date_published;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('content', new Constraints\NotBlank())
            ->addPropertyConstraint('language', new Constraints\NotBlank())

            ->addGetterConstraint('slug', new Constraints\NotBlank())
            ->addGetterConstraint('slug', new Constraints\Regex(array(
                'pattern' => '/^[a-z0-9-]+$/',
            )))

            ->addGetterConstraint('statusValid', new Constraints\True(array(
                'message' => sprintf('Value is not valid, use one of (%s): ', implode(', ', self::getValidStatuses())),
            )))
        ;
    }
}
