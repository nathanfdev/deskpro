<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback;

use Application\DeskPRO\Entity\CustomDataFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\ContentAbstract;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Feedback.
 */
class Feedback extends ContentAbstract
{
    /**
     * Category the feedback belongs to.
     *
     * @JMS\Type("entity<Application\DeskPRO\Entity\FeedbackStatusCategory>")
     *
     * @var \Application\DeskPRO\Entity\FeedbackStatusCategory
     */
    protected $statusCategory = null;

    /**
     * Category the feedback belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("entity<Application\DeskPRO\Entity\FeedbackCategory>")
     *
     * @var \Application\DeskPRO\Entity\FeedbackCategory
     */
    protected $category;

    /**
     * String array of labels associated with this news.
     *
     * @JMS\Groups({"labels"})
     * @JMS\Type("array<label<Application\DeskPRO\Entity\LabelFeedback>>")
     *
     * @var \Application\DeskPRO\Entity\LabelFeedback
     */
    protected $labels;

    /**
     * Custom ticket fields.
     *
     * @JMS\Expose()
     * @JMS\Type("custom_data<array<Application\DeskPRO\Entity\CustomDataFeedback>>")
     *
     * @var CustomDataFeedback[]
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
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\Feedback $entity
     */
    public function __construct(\Application\DeskPRO\Entity\Feedback $entity)
    {
        parent::__construct($entity);

        $this->statusCategory = $entity->getStatusCategory();
        $this->category       = $entity->getCategory();
        $this->labels         = $entity->getLabels();
        $this->fields         = $entity->getCustomData();
        $this->popularity     = $entity->getPopularity();
        $this->isReviewed     = $entity->isReviewed();
    }

    /**
     * @param mixed $commentsCount
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
    }
}
