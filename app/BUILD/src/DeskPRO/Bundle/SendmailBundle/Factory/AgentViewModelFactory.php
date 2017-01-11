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

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;
use DateTime;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\SendmailBundle\View\Model\AdminNoResetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentChangeEmailMergeUser;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorInvalidForward;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorMarkerMissing;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorUnknownFrom;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentLoginAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewChatMessage;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewComment;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewFeedback;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewRegistration;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentPasswordResetAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcome;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcomeUsersource;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class AgentViewModelFactory
{
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var ObjectRouter
     */
    private $objectRouter;

    /**
     * Constructor.
     *
     * @param RouterInterface $router
     * @param ObjectRouter    $objectRouter
     */
    public function __construct(RouterInterface $router, ObjectRouter $objectRouter)
    {
        $this->router       = $router;
        $this->objectRouter = $objectRouter;
    }

    /**
     * @return AdminNoResetPassword
     */
    public function createAdminNoResetPasswordModel()
    {
        return new AdminNoResetPassword();
    }

    /**
     * @param $oldEmail
     * @param $newEmail
     *
     * @return AgentChangeEmailMergeUser
     */
    public function createAgentChangeEmailMergeUserModel($oldEmail, $newEmail)
    {
        return new AgentChangeEmailMergeUser($oldEmail, $newEmail);
    }

    /**
     * @return AgentErrorInvalidForward
     */
    public function createAgentErrorInvalidForwardModel()
    {
        return new AgentErrorInvalidForward();
    }

    /**
     * @return AgentErrorMarkerMissing
     */
    public function createAgentErrorMarkerMissingModel()
    {
        return new AgentErrorMarkerMissing();
    }

    /**
     * @return AgentErrorUnknownFrom
     */
    public function createAgentErrorUnknownFromModel()
    {
        return new AgentErrorUnknownFrom();
    }

    /**
     * @param ChatMessage $chatMessage
     *
     * @return AgentNewChatMessage
     */
    public function createAgentNewChatMessageModel(ChatMessage $chatMessage)
    {
        return new AgentNewChatMessage($chatMessage);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return AgentNewComment
     */
    public function createAgentNewCommentModel(CommentAbstract $comment)
    {
        return new AgentNewComment($this->router, $comment);
    }

    /**
     * @param Feedback $feedback
     *
     * @return AgentNewFeedback
     */
    public function createAgentNewFeedbackModel(Feedback $feedback)
    {
        return new AgentNewFeedback($this->router, $feedback);
    }

    /**
     * @param Person $person
     *
     * @return AgentNewRegistration
     */
    public function createAgentNewRegistrationModel(Person $person)
    {
        return new AgentNewRegistration($this->router, $person);
    }

    /**
     * @param Person $performer
     * @param $newPassword
     *
     * @return AgentPasswordResetAlert
     */
    public function createAgentPasswordResetAlertModel(Person $performer, $newPassword)
    {
        return new AgentPasswordResetAlert($this->router, $performer, $newPassword);
    }

    /**
     * @return AgentWelcome
     */
    public function createAgentWelcomeModel()
    {
        return new AgentWelcome($this->router);
    }

    /**
     * @return AgentWelcomeUsersource
     */
    public function createAgentWelcomeUsersourceModel()
    {
        return new AgentWelcomeUsersource($this->router);
    }

    /**
     * @param DateTime $firstSeen
     * @param bool     $success
     * @param Request  $request
     *
     * @return AgentLoginAlert
     */
    public function createLoginAlertModel(Request $request, $firstSeen, $success)
    {
        return new AgentLoginAlert($request, $firstSeen, $success);
    }
}
