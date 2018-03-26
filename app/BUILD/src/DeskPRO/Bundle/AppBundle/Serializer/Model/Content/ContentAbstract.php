<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Content;

use Application\DeskPRO\Entity\ContentAbstract as ContentAbstractEntity;
use JMS\Serializer\Annotation as JMS;

/**
 * Class ContentAbstract.
 */
abstract class ContentAbstract
{
    /**
     * The unique ID.
     *
     * @JMS\Groups({"labels"})
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $id = null;

    /**
     * Person created this content first time.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Person>")
     *
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * Language content was written.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\Language>")
     *
     * @var \Application\DeskPRO\Entity\Language
     */
    protected $language = null;

    /**
     * Content slug.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $slug;

    /**
     * Content title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $title;

    /**
     * The main content for the item. This should be HTML!
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content = '';

    /**
     * View counts.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $viewCount = 0;

    /**
     * Total rating: This is a tally and must be updated when a rating is added.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $totalRating = 0;

    /**
     * Number of user-visible comments: This is a count that must be updated when a comment is added.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $numComments = 0;

    /**
     * Total rating.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $numRatings = 0;

    /**
     * Status title.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $status;

    /**
     * Hidden status code.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $hiddenStatus = null;

    /**
     * DateTime when content was created.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * DateTime when content was updated last time.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $dateUpdated;

    /**
     * DateTime when content was published.
     *
     * @JMS\Type("DateTime")
     *
     * @var \DateTime
     */
    protected $datePublished;

    /**
     * Vote stats object, like {"up": 1, "down": 1}.
     *
     * @JMS\Type("array")
     */
    protected $voteStats;

    /**
     * Revisions of this article.
     *
     * @JMS\Expose()
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\ArticleRevision>>")
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $revisions;

    /**
     * Constructor.
     *
     * @param ContentAbstractEntity $entity
     */
    public function __construct(ContentAbstractEntity $entity)
    {
        $this->id            = $entity->getId();
        $this->person        = $entity->getPerson();
        $this->language      = $entity->getLanguage();
        $this->slug          = $entity->getSlug();
        $this->title         = $entity->getTitle();
        $this->content       = $entity->getRealContent();
        $this->viewCount     = $entity->getViewCount();
        $this->totalRating   = $entity->getTotalRating();
        $this->numComments   = $entity->getNumComments();
        $this->numRatings    = $entity->getNumRatings();
        $this->status        = $entity->getStatus();
        $this->hiddenStatus  = $entity->getHiddenStatus();
        $this->dateCreated   = $entity->getDateCreated();
        $this->dateUpdated   = $entity->getDateUpdated();
        $this->datePublished = $entity->getDatePublished();
        $this->voteStats     = $entity->getVoteStats();
        $this->revisions     = $entity->getRevisions();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }
}
