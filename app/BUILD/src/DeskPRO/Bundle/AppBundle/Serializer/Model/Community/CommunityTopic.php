<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Community;

use Application\DeskPRO\Entity\CommunityTopicAttachment;
use Application\DeskPRO\Entity\CustomDataCommunityTopic;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Feedback.
 */
class CommunityTopic extends ContentAbstract
{
    /**
     * Category the feedback belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\CommunityTopicStatusCategory>")
     *
     * @var \Application\DeskPRO\Entity\CommunityTopicStatusCategory
     */
    protected $statusCategory = null;

    /**
     * Category the feedback belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\CommunityChannel>")
     *
     * @var \Application\DeskPRO\Entity\CommunityChannel
     */
    protected $category;

    /**
     * String array of labels associated with this news.
     *
     * @JMS\Groups({"labels"})
     * @JMS\Type("array<label<Application\DeskPRO\Entity\LabelFeedback>>")
     *
     * @var \Application\DeskPRO\Entity\LabelCommunityTopic
     */
    protected $labels;

    /**
     * Custom ticket fields.
     *
     * @JMS\Expose()
     * @JMS\Type("custom_data<array<Application\DeskPRO\Entity\CustomDataCommunityTopic>>")
     *
     * @var CustomDataCommunityTopic[]
     */
    protected $fields;

    /**
     * Popularity.
     *
     * @JMS\Type("integer")
     *
     * @var int
     */
    protected $popularity = 0;

    /**
     * Comments count.
     *
     * @JMS\Type("deferred<integer>")
     *
     * @var int
     */
    protected $commentsCount;

    /**
     * Is reviewed.
     *
     * @JMS\Type("boolean")
     *
     * @var bool
     */
    protected $isReviewed;

    /**
     * Items attached to the content.
     *
     * @JMS\Type("collection<entity<Application\DeskPRO\Entity\FeedbackAttachment>>")
     *
     * @var CommunityTopicAttachment[]
     */
    protected $attachments;

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\CommunityTopic $entity
     */
    public function __construct(\Application\DeskPRO\Entity\CommunityTopic $entity)
    {
        parent::__construct($entity);

        $this->statusCategory = $entity->getStatusCategory();
        $this->category       = $entity->getCategory();
        $this->labels         = $entity->getLabels();
        $this->fields         = $entity->getCustomData();
        $this->popularity     = $entity->getPopularity();
        $this->isReviewed     = $entity->isReviewed();
        $this->attachments    = $entity->getAttachments();
    }

    /**
     * @param mixed $commentsCount
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
    }
}
