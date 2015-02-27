<?php

namespace Application\ImportBundle\Entity;

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
            return 'published';
        }

        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setStatus($status)
    {
        $this->status = $status;

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
    public function setDatePublished(DateTime $date_published)
    {
        $this->date_published = $date_published;

        return $this;
    }

    /**
     * Validator class metadata.
     *
     * @param ClassMetadata $metadata
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
            )));
    }
}
