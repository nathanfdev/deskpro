<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class Article
 * @package Application\ImportBundle\Entity
 */
class Article extends AbstractEntity
{
    /**
     * @var int
     */
    private $oid;

    /**
     * @var string
     */
    private $person_email;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $content;

    /**
     * @var int
     */
    private $total_rating = 0;

    /**
     * @var int
     */
    private $num_comments = 0;

    /**
     * @var int
     */
    private $num_ratings = 0;

    /**
     * @var string[]
     */
    private $categories = array();

    /**
     * @var string[]
     */
    private $labels = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_ARTICLE;
    }

    /**
     * @return int
     */
    public function getOid()
    {
        return $this->oid;
    }

    /**
     * @param int $oid
     * @return $this
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
        return $this;
    }

    /**
     * @return string
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @param string $person_email
     * @return $this
     */
    public function setPersonEmail($person_email)
    {
        $this->person_email = $person_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return int
     */
    public function getTotalRating()
    {
        return $this->total_rating;
    }

    /**
     * @param int $total_rating
     * @return $this
     */
    public function setTotalRating($total_rating)
    {
        $this->total_rating = $total_rating;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumComments()
    {
        return $this->num_comments;
    }

    /**
     * @param int $num_comments
     * @return $this
     */
    public function setNumComments($num_comments)
    {
        $this->num_comments = $num_comments;
        return $this;
    }

    /**
     * @return int
     */
    public function getNumRatings()
    {
        return $this->num_ratings;
    }

    /**
     * @param int $num_ratings
     * @return $this
     */
    public function setNumRatings($num_ratings)
    {
        $this->num_ratings = $num_ratings;
        return $this;
    }

    /**
     * Returns the labels
     *
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function addLabel($label)
    {
        $this->labels[] = $label;
        return $this;
    }

    /**
     * Returns the labels
     *
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function addCategory($category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'          => $this->oid,
            'person'       => $this->person_email,
            'language'     => $this->language,
            'title'        => $this->title,
            'content'      => $this->content,
            'categories'   => $this->categories,
            'labels'       => $this->labels,
            'total_rating' => $this->total_rating,
            'num_comments' => $this->num_comments,
            'num_ratings'  => $this->num_ratings,
        );
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('content', new Constraints\NotBlank())
        ;
    }
}
