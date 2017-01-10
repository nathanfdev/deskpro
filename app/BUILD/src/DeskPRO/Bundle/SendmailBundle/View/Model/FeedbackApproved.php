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

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Routing\RouterInterface;

class FeedbackApproved extends EmailBaseType
{
    /**
     * The feedback that has been approved.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Feedback\Feedback")
     *
     * @var Feedback
     */
    protected $feedback;

    /**
     * The agent that approved the feedback.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $agent;

    /**
     * A link to the feedback.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $feedbackLink;

    protected static $templateFile = 'emails_user:feedback_approved.html.twig';

    /**
     * FeedbackApproved constructor.
     *
     * @param RouterInterface $router
     * @param Feedback        $feedback
     * @param Person          $agent
     */
    public function __construct(RouterInterface $router, Feedback $feedback, Person $agent)
    {
        $this->feedback     = $feedback;
        $this->agent        = $agent;
        $this->feedbackLink = $router->generate('user_feedback_view', ['slug' => $feedback->getSlug()]);
    }
}
