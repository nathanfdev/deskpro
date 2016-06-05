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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback;

use Application\DeskPRO\Entity\CustomDataFeedback;
use DeskPRO\Bundle\AppBundle\Serializer\Model\ContentAbstract;
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
     * @JMS\Type("array<to_string<Application\DeskPRO\Entity\LabelFeedback>>")
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
