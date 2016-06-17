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

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Comment;

use Application\DeskPRO\Entity\FeedbackComment as CommentEntity;
use JMS\Serializer\Annotation as JMS;

class FeedbackCommentCsv extends FeedbackComment
{
    /**
     * Name of comment's author.
     *
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * ID of feedback this comment belongs to.
     *
     * @JMS\Type("integer")
     */
    private $feedbackId;

    /**
     * Title of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $title;

    /**
     * Content of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $feedbackContent;

    /**
     * Status of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $feedbackStatus;

    /**
     * Hidden status of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $hiddenStatus;

    /**
     * Category of feedback this comment belongs to.
     *
     * @JMS\Type("string")
     */
    private $category;

    /**
     * Constructor.
     *
     * @param CommentEntity $entity
     */
    public function __construct($entity)
    {
        parent::__construct($entity);
        $this->person          = $entity->getPerson() ? $entity->getPerson()->getName() : '';
        $this->content         = mb_substr($entity->getContent(), 0, 50);
        $this->feedbackId      = $this->feedback->getId();
        $this->title           = $this->feedback->getTitle();
        $this->feedbackContent = mb_substr($this->feedback->getRealContent(), 0, 50);
        $this->feedbackStatus  = $this->feedback->getStatusCategory() ? $this->feedback->getStatusCategory()
            ->getTitle() : '';
        $this->hiddenStatus = $this->feedback->getHiddenStatus() ?: '';
        $this->category     = $this->feedback->getCategory() ? $this->feedback->getCategory()->getTitle() : '';
    }
}
