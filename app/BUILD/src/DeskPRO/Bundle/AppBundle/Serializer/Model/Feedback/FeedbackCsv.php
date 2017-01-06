<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
