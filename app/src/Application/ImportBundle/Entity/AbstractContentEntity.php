<?php

namespace Application\ImportBundle\Entity;

use Orb\Util\Strings;

/**
 * Basic properties on content
 *
 * Class AbstractContentEntity
 * @package Application\ImportBundle\Entity
 *
 * todo add slug validator
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
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * {@inheritdoc}
     */
    public function setViewCount($view_count)
    {
        $this->view_count = (int)$view_count;
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
        $this->total_rating = (int)$total_rating;
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
        $this->num_comments = (int)$num_comments;
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
        $this->num_ratings = (int)$num_ratings;
        return $this;
    }
}
