<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

/**
 * DeskPRO.
 *.
 *
 * @category Entities
 */
namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use DpSys\LowError\SystemErrorHandler;
use JMS\Serializer\Annotation as JMS;
use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Basic properties on content.
 *
 * @JMS\ExclusionPolicy("all")
 */
abstract class ContentAbstract extends \Application\DeskPRO\Domain\DomainObject
{
    const CONTENT_TYPE = null;

    const STATUS_PUBLISHED = 'published';

    const STATUS_ARCHIVED = 'archived';

    const STATUS_HIDDEN = 'hidden';

    const HIDDEN_STATUS_UNPUBLISHED = 'unpublished';

    const HIDDEN_STATUS_DELETED = 'deleted';

    const HIDDEN_STATUS_SPAM = 'spam';

    const HIDDEN_STATUS_DRAFT = 'draft';

    /**
     * The unqique ID.
     *
     * @JMS\Expose()
     * @JMS\Groups({"labels"})
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Person created this content first time.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var Person
     */
    protected $person = null;

    /**
     * Language content was written.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var Language
     */
    protected $language = null;

    /**
     * Content slug.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $slug;

    /**
     * Content title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * The main content for the item. This should be HTML!
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content = '';

    /**
     * View counts.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $view_count = 0;

    /**
     * Total rating: This is a tally and must be updated when a rating is added.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $total_rating = 0;

    /**
     * @var ArrayCollection
     */
    protected $comments;

    /**
     * Number of user-visible comments: This is a count that must be updated when a comment is added.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $num_comments = 0;

    /**
     * Total rating.
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $num_ratings = 0;

    /**
     * Status title.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status;

    /**
     * @var string
     */
    protected $hidden_status = null;

    /**
     * DateTime when content was created.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var DateTime
     */
    protected $date_created;

    /**
     * @var DateTime
     */
    protected $date_published;

    /**
     * @var \DateTime
     */
    protected $date_last_comment;

    /**
     * DateTime when content was updated last time.
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $date_updated;

    // Implement in children
    ///**
    // * @var ArrayCollection
    // */
    //protected $revisions;

    // Implement in children
    ///**
    // */
    //protected $labels;

    /**
     * An array of authors,.
     */
    protected $_authors = null;

    /**
     * @var \Application\DeskPRO\Labels\LabelManager
     */
    protected $_label_manager = null;

    /**
     */
    protected $slug_history;

    /**
     * @return array
     */
    public static function getAllStatuses()
    {
        return [
            self::STATUS_PUBLISHED,
            self::STATUS_ARCHIVED,
            self::STATUS_HIDDEN,
        ];
    }

    /**
     * @return array
     */
    public static function getAllHiddenStatuses()
    {
        return [
            self::HIDDEN_STATUS_UNPUBLISHED,
            self::HIDDEN_STATUS_DELETED,
            self::HIDDEN_STATUS_SPAM,
            self::HIDDEN_STATUS_DRAFT,
        ];
    }

    public function __construct()
    {
        $this->setModelField('date_updated', new \DateTime());
        $this['date_created'] = new \DateTime();
        $this->revisions      = new ArrayCollection();
        $this->labels         = new ArrayCollection();
        $this->slug_history   = new ArrayCollection();
        $this->comments       = new ArrayCollection();

        $this['status']        = self::STATUS_HIDDEN;
        $this['hidden_status'] = self::HIDDEN_STATUS_DRAFT;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Check if this content is publicly visible (i.e. not spam, not a draft, etc).
     * 
     * @return bool
     */
    public function isPublic()
    {
        return $this->status === self::STATUS_PUBLISHED || $this->status === self::STATUS_ARCHIVED;
    }

    /**
     * @return \DateTime
     */
    public function getDateLastComment()
    {
        return $this->date_last_comment;
    }

    /**
     * @return DateTime
     */
    public function getDateUpdated()
    {
        return $this->date_updated;
    }

    /**
     * @param DateTime $date_updated
     *
     * @return $this
     */
    public function setDateUpdated(DateTime $date_updated = null)
    {
        $this->setModelField('date_updated', $date_updated);

        return $this;
    }

    /**
     * @deprecated use $this->get('object_router')->getPortalPath($this) instead
     */
    public function getPath()
    {
        SystemErrorHandler::logExceptionIfUniqueBacktrace(
            new \Exception('DEPRECATED METHOD CALL: '.get_called_class().'::getPath()')
        );

        return App::getObjectRouter()->getPortalPath($this);
    }

    /**
     * @deprecated use $this->get('object_router')->getPortalUrl($this) instead
     *
     * @return $this
     */
    public function getLink()
    {
        return App::getObjectRouter()->getPortalUrl($this);
    }

    /**
     * @return string
     *
     * @deprecated use $this->get('object_router')->getPortalUrl($this, 'permalink') instead
     */
    public function getPermalink($absolute = true)
    {
        if ($absolute) {
            return App::getObjectRouter()->getPortalUrl($this, 'permalink');
        }

        return App::getObjectRouter()->getPortalPath($this, 'permalink');
    }

    public function setTitle($title)
    {
        $old_title = $this->title;
        $this->setModelField('title', $title);

        // note: removed the setSlug call, we do that in the DoctrineContentSlugListener now (prepersist/preupdate)
        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTranslatedTitle()
    {
        return $this->__call('getTitle', []);
    }

    /**
     * @return string
     */
    public function getTranslatedContent()
    {
        return $this->__call('getContent', []);
    }

    /**
     * @param Language $language
     *
     * @return $this
     */
    public function setLanguage(Language $language = null)
    {
        $this->setModelField('language', $language);

        return $this;
    }

    public function getLanguage()
    {
        if ($this->language) {
            return $this->language;
        }

        return App::getContainer()->getLanguageData()->getDefault();
    }

    public function getRealLanguage()
    {
        return $this->language;
    }

    public function setStatus($status)
    {
        $this->setStatusCode($status);
    }

    public function setStatusCode($status_code)
    {
        if (strpos($status_code, 'hidden.') === 0) {
            $status_code = str_replace('hidden.', '', $status_code);
            $this->setModelField('status', 'hidden');
            $this->setModelField('hidden_status', $status_code);
        } else {
            $this->setModelField('status', $status_code);
            $this->setModelField('hidden_status', null);

            if (!$this->date_published) {
                $this->setModelField('date_published', new \DateTime());
            }
        }
    }

    public function getStatusCode()
    {
        if ($this->hidden_status) {
            return 'hidden.'.$this->hidden_status;
        } else {
            return $this->status;
        }
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getHiddenStatus()
    {
        return $this->hidden_status;
    }

    public function contentModifier($content)
    {
        // Find attach replacements: ![attach:{$blob['authcode']}:{$blob['filename']}]
        $fn = function ($m) {
            return App::getSetting('core.deskpro_url').'file.php/'.$m[1].'/'.urlencode($m[2]);
        };
        $content = preg_replace_callback('#!\[attach:([0-9A-Z]+):(.*?)\]#', $fn, $content);

        return $content;
    }

    public function setContent($content)
    {
        if (!$content) {
            $content = '';
        }

        $this->setModelField('content', $content);

        return $this;
    }

    public function getContentHtml()
    {
        return $this['content'];
    }

    public function getContentPlainHtml()
    {
        $content = htmlspecialchars($this['content']);
        $content = nl2br($content);

        return $content;
    }

    public function getContentPlain()
    {
        $content = $this['content'];
        if (!$content) {
            return '';
        }
        $content = Strings::standardEol($this['content']);
        $content = preg_replace("#<br\s*/?><p>#", '<p>', $content);
        $content = preg_replace("#<p></p><br\s*/?>#", '<p>', $content);
        $content = preg_replace("#</p><br\s*/?>#", '</p>', $content);
        $content = preg_replace("#<br\s*/?></p>#", '</p>', $content);
        $content = preg_replace("#<br\s*/?>?#", "\n", $content);
        $content = preg_replace("#<p>\n?#", "\n", $content);
        $content = preg_replace("#\n?</p>#", "\n", $content);
        $content = html_entity_decode(Strings::stripTags($content), \ENT_QUOTES, 'UTF-8');
        $content = str_replace('&nbsp;', ' ', $content);
        $content = trim($content);

        $lines_raw = explode("\n", $content);
        $lines     = [];
        foreach ($lines_raw as $l) {
            $lines[] = trim($l);
        }

        $content = implode("\n", $lines);
        $content = preg_replace("#\n{3,}#", "\n\n", $content);

        return $content;
    }

    /**
     * Get an excerpt of the content suitable for display in a search listing. So this means
     * no html, and collapsed whitespace.
     */
    public function getSearchSummary($length = 100)
    {
        $content = $this->getContentPlain();
        $content = str_replace(["\r\n", "\n"], ' ', $content);

        if (Strings::utf8_strlen($content) > $length) {
            $content = Strings::utf8_substr($content, 0, $length).'...';
        }

        return $content;
    }

    /**
     * @return string
     *
     * @deprecated use getSlug() instead (we no longer do the id-slug format in portal)
     */
    public function getUrlSlug()
    {
        return $this->id.'-'.$this->slug;
    }

    /**
     * @return ArrayCollection
     */
    public function getSlugHistory()
    {
        return $this->slug_history;
    }

    /**
     * NOTE: don't use this directly. Instead, use the "content_slug_manager" service to set the slug for you.
     *
     * @param $new_slug
     *
     * @internal this shouldn't be called except by the content_slug_manager
     */
    public function setSlug($new_slug)
    {
        $history = null;
        if ($new_slug !== $this->slug && $this->slug) {
            // if the slug exists in history already, we don't want to add it again
            $object_slug = $this->slug;
            if (!$this->slug_history->exists(
                function ($key, $history) use ($object_slug) {
                    return $object_slug === $history->getSlug();
                }
            )
            ) {
                $history = $this->addSlugHistory($this->slug);
            }
        }
        $this->setModelField('slug', $new_slug);

        return $history;
    }

    abstract protected function addSlugHistory($old_slug);

    /**
     * Get an array of authors.
     *
     * @return array
     */
    public function getAuthors()
    {
        if ($this->_authors !== null) {
            return $this->_authors;
        }

        $this->_authors = [];

        if ($this->person) {
            $this->_authors[$this->person['id']] = $this->person;
        }

        $ent   = $this->getEntityName().'Revision';
        $field = strtolower(str_replace('DeskPRO:', '', $this->getEntityName()));

        $revs = App::getOrm()->createQuery(
            "
            SELECT r, p
            FROM $ent r
            LEFT JOIN r.person p
            WHERE r.$field = ?1 AND r.person IS NOT NULL
            ORDER BY r.date_created DESC
        "
        )->setParameter(1, $this)->execute();

        foreach ($revs as $r) {
            if ($r->person) {
                $this->_authors[$r->person->id] = $r->person;
            }
        }

        return $this->_authors;
    }

    /**
     * Last author touched this content.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     * @JMS\SerializedName("last_author_id")
     */
    public function getLastAuthor()
    {
        $authors = $this->getAuthors();
        $author  = end($authors);

        return $author ?: null;
    }

    public function getByLine($sep = ', ')
    {
        $names = [];
        foreach ($this->getAuthors() as $a) {
            $names[] = $a->getDisplayName();
        }

        return implode($sep, $names);
    }

    /**
     * Vote stats object, like {"up": 1, "down": 1}.
     *
     * @JMS\VirtualProperty()
     * @JMS\Type("array")
     */
    public function getVoteStats()
    {
        $x = $this->num_ratings - abs($this->total_rating);

        if ($x % 2 == 1) {
            ++$x; // never happens with correct data, this just error corrects
        }

        if ($this->total_rating >= 0) {
            $up   = ($x / 2) + $this->total_rating;
            $down = ($x / 2);
        } else {
            $up   = ($x / 2);
            $down = ($x / 2) + abs($this->total_rating);
        }

        return ['up' => $up, 'down' => $down];
    }

    public function getUpVotes()
    {
        $stats = $this->getVoteStats();

        return $stats['up'];
    }

    public function getDownVotes()
    {
        $stats = $this->getVoteStats();

        return $stats['down'];
    }

    public function markRatingChangedPositivly()
    {
        // 2 to override the -1 when the neg rating was added
        $this['total_rating'] = $this->total_rating + 2;
    }

    public function markRatingChangedNegatively()
    {
        // 2 to override the -1 when the positive rating was added
        $this['total_rating'] = $this->total_rating - 2;
    }

    public function getRatingPercent()
    {
        if (!$this->num_ratings) {
            return 0;
        }

        return min(100, ceil(($this->total_rating / $this->num_ratings) * 100));
    }

    public function addRating($rating)
    {
        $this['num_ratings']  = $this->num_ratings + 1;
        $this['total_rating'] = $this->total_rating + $rating->rating;
        $rating->setContentObject($this);
    }

    public function removeRating($rating)
    {
        $this['num_ratings']  = $this->num_ratings - 1;
        $this['total_rating'] = $this->total_rating - $rating->rating;
    }

    /**
     * @param CommentAbstract $comment
     */
    public function addComment($comment)
    {
        if ($comment->getStatus() == CommentAbstract::STATUS_VISIBLE) {
            $this->setModelField('num_comments', $this->num_comments + 1);
            $this->setDateUpdated();
        }
        $this->setModelField('date_last_comment', new \DateTime());
        $comment->setObject($this);
    }

    public function removeComment()
    {
        $this->setModelField('num_comments', $this->num_comments - 1);
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setNumComments($value)
    {
        $this->setModelField('num_comments', $value);

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
     * @return int
     */
    public function getNumRatings()
    {
        return $this->num_ratings;
    }

    /**
     * @return \Application\DeskPRO\Labels\LabelManager
     */
    public function getLabelManager()
    {
        if ($this->_label_manager === null) {
            $name                 = Util::getBaseClassname($this);
            $this->_label_manager = new \Application\DeskPRO\Labels\LabelManager($this, 'DeskPRO:Label'.$name);
        }

        return $this->_label_manager;
    }

    /**
     * @return string
     */
    public static function getContentType()
    {
        static $name = null;

        if ($name === null) {
            $name = self::getEntityName();
            $name = strtolower(str_replace('DeskPRO:', '', $name));
        }

        return $name;
    }

    /**
     * @return string
     */
    public function getRealTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getRealContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function setRealTitle($title)
    {
        $this->setModelField('title', $title);
    }

    /**
     * @return string
     */
    public function setRealContent($content)
    {
        $this->setModelField('content', $content);
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return Person
     * @return $this
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person = null)
    {
        $this->setModelField('person', $person);

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDatePublished()
    {
        return $this->date_published;
    }

    /**
     * @return \DateTime
     */
    public function getDateCreated()
    {
        return $this->date_created;
    }

    /**
     * @return int
     */
    public function getTotalRating()
    {
        return $this->total_rating;
    }

    /**
     * @return int
     */
    public function getViewCount()
    {
        return $this->view_count;
    }

    /**
     * @return string
     */
    public function getContentDesc()
    {
        $content = $this->content;
        $content = Strings::html2Text($content);
        $content = str_replace("\n", ' ', $content);
        $content = preg_replace('# {2,}#', ' ', $content);

        if (strlen($content) > 120) {
            $content = substr($content, 0, 120).'...';
        }

        return $content;
    }

    protected function getUpdateFields()
    {
        return [
            'title',
            'content',
            'status',
        ];
    }

    public function _preUpdate()
    {
        foreach ($this->getStateChangeRecorder()->getTouchedFields() as $touched_field) {
            if (in_array($touched_field, $this->getUpdateFields())) {
                $this->setDateUpdated(new DateTime());

                return true;
            }
        }
    }
}
