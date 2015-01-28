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
     */
    public function setOid($oid)
    {
        $this->oid = $oid;
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
     */
    public function setCategory($category)
    {
        $this->category = $category;
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
     */
    public function setBlob($blob)
    {
        $this->blob = $blob;
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
     */
    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
    }

    /**
     * @return string
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
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
     */
    public function setLanguage($language)
    {
        $this->language = $language;
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
     */
    public function setNumDownloads($num_downloads)
    {
        $this->num_downloads = $num_downloads;
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
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
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
     */
    public function setTitle($title)
    {
        $this->title = $title;
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
     */
    public function setContent($content)
    {
        $this->content = $content;
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
     */
    public function setViewCount($view_count)
    {
        $this->view_count = $view_count;
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
     */
    public function setTotalRating($total_rating)
    {
        $this->total_rating = $total_rating;
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
     */
    public function setNumComments($num_comments)
    {
        $this->num_comments = $num_comments;
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
     */
    public function setNumRatings($num_ratings)
    {
        $this->num_ratings = $num_ratings;
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
     */
    public function setStatus($status)
    {
        $this->status = $status;
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
     */
    public function setDateCreated(DateTime $date_created)
    {
        $this->date_created = $date_created;
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
     */
    public function setDatePublished(DateTime $date_published)
    {
        $this->date_published = $date_published;
    }

    /**
     * @return array
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * @param array $labels
     */
    public function setLabels($labels)
    {
        $this->labels = $labels;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array();
    }

    /**
     * Validator class metadata
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {

    }
}
