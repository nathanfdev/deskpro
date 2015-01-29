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
use DateTime;

/**
 * Class Download
 * @package Application\ImportBundle\Entity
 */
final class Download extends AbstractEntity
{
    /**
     * @var int
     */
    public $oid;

    /**
     * @var string
     */
    public $category;

    /**
     * @var int
     */
    public $blob;

    /**
     * @var AttachmentValue
     */
    public $attachment;

    /**
     * @var string
     */
    public $person;

    /**
     * @var string
     */
    public $language;

    /**
     * @var int
     */
    public $num_downloads = 0;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * @var int
     */
    public $view_count = 0;

    /**
     * @var int
     */
    public $total_rating = 0;

    /**
     * @var int
     */
    public $num_comments = 0;

    /**
     * @var int
     */
    public $num_ratings = 0;

    /**
     * @var string
     */
    public $status;

    /**
     * @var DateTime
     */
    public $date_created;

    /**
     * @var DateTime
     */
    public $date_published;

    /**
     * @var array
     */
    public $labels = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_DOWNLOAD;
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
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return int
     */
    public function getBlob()
    {
        return $this->blob;
    }

    /**
     * @param int $blob
     * @return $this
     */
    public function setBlob($blob)
    {
        $this->blob = $blob;
        return $this;
    }

    /**
     * @return AttachmentValue
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param AttachmentValue $attachment
     * @return $this
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
        return $this;
    }

    /**
     * @return string
     */
    public function getPersonEmail()
    {
        return $this->person;
    }

    /**
     * @param string $person_email
     * @return $this
     */
    public function setPersonEmail($person_email)
    {
        $this->person = $person_email;
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
     * @return int
     */
    public function getNumDownloads()
    {
        return $this->num_downloads;
    }

    /**
     * @param int $num_downloads
     * @return $this
     */
    public function setNumDownloads($num_downloads)
    {
        $this->num_downloads = $num_downloads;
        return $this;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * @param int $view_count
     * @return $this
     */
    public function setViewCount($view_count)
    {
        $this->view_count = $view_count;
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
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @param DateTime $date_created
     * @return $this
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDatePublished()
    {
        return $this->date_published;
    }

    /**
     * @param DateTime $date_published
     * @return $this
     */
    public function setDatePublished(DateTime $date_published)
    {
        $this->date_published = $date_published;
        return $this;
    }

    /**
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
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'title'   => $this->title,
            'content' => $this->content,
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
