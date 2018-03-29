<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback;

use Application\DeskPRO\Entity\Feedback as FeedbackEntity;
use JMS\Serializer\Annotation as JMS;

class FeedbackCsv extends Feedback
{
    /**
     * Author's name.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $person;

    /**
     * Category the feedback belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $statusCategory = null;

    /**
     * Category the feedback belongs to.
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     */
    protected $category;

    /**
     * The main content for the item.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $content = '';

    /**
     * Constructor.
     *
     * @param \Application\DeskPRO\Entity\Feedback $feedback
     */
    public function __construct(FeedbackEntity $feedback)
    {
        parent::__construct($feedback);

        $this->person         = $feedback->getByLine();
        $this->statusCategory = $feedback->getStatusCategory() ? $feedback->getStatusCategory()->getTitle() : '';
        $this->category       = $feedback->getCategory() ? $feedback->getCategory()->getTitle() : '';
        $this->content        = mb_substr($feedback->getContentPlain(), 0, 120);
    }
}
