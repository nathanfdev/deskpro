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
use Orb\Util\Strings;

/**
 * Exporting download entity
 *
 * Class Download
 * @package Application\ImportBundle\Entity
 */
final class Download extends AbstractEntity implements SlugAwareInterface, PersonAwareInterface, LabelAwareInterface
{
    /**
     * @var string
     */
    private $category;

    /**
     * @var Attachment
     */
    private $attachment;

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
    private $slug;

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
    private $view_count = 0;

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
     * @var int
     */
    private $num_downloads = 0;

    /**
     * @var string
     */
    private $status;

    /**
     * @var DateTime
     */
    private $date_created;

    /**
     * @var DateTime
     */
    private $date_published;

    /**
     * @var array
     */
    private $labels = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_DOWNLOAD;
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
     * @return Attachment
     */
    public function getAttachment()
    {
        return $this->attachment;
    }

    /**
     * @param Attachment $attachment
     * @return $this
     */
    public function setAttachment(Attachment $attachment = null)
    {
        $this->attachment = $attachment;
        return $this;
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
        $this->total_rating = (int)$total_rating;
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
        $this->num_comments = (int)$num_comments;
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
        $this->num_ratings = (int)$num_ratings;
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
    public function getStatus()
    {
        if ($this->date_published) {
            return 'published';
        }

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
     * {@inheritdoc}
     */
    public function getLabels()
    {
        return $this->labels;
    }

    /**
     * {@inheritdoc}
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
        if (!$this->date_created) {
            throw new \Exception('Date created is not set up');
        }

        return array(
            'oid'            => $this->oid,
            'person'         => $this->person_email,
            'title'          => $this->title,
            'content'        => $this->content,
            'language'       => $this->language,
            'slug'           => $this->slug,
            'total_rating'   => $this->total_rating,
            'num_comments'   => $this->num_comments,
            'num_ratings'    => $this->num_ratings,
            'num_downloads'  => $this->num_downloads,
            'view_count'     => $this->view_count,
            'category'       => $this->category,
            'status'         => $this->status,
            'attachment'     => $this->attachment ? $this->attachment->toArray() : null,
            'date_created'   => $this->date_created->format('Y-m-d H:i:s'),
            'date_published' => $this->date_published ? $this->date_published->format('Y-m-d H:i:s') : null,
            'labels'         => $this->labels,
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
            ->addPropertyConstraint('oid', new Constraints\NotBlank())
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('content', new Constraints\NotBlank())
            ->addPropertyConstraint('attachment', new Constraints\NotNull())
        ;
    }
}
