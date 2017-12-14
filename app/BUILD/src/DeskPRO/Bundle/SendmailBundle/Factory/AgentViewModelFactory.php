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
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use DateTime;
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
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTaskAssigned;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTaskCompleted;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTaskDueReminder;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketForward;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketNew;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketReply;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentTicketUpdate;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcome;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWelcomeUsersource;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentWhitelistIp;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AgentViewModelFactory extends AbstractViewModelFactory
{
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
        return $this->convertParameters(AgentChangeEmailMergeUser::class, [$oldEmail, $newEmail]);
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
        if ($chatMessage->getAuthor()) {
            $author = $chatMessage->getAuthor();
        } else {
            $author = null;
        }

        return $this->convertParameters(AgentNewChatMessage::class, [$chatMessage, $author]);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return AgentNewComment
     */
    public function createAgentNewCommentModel(CommentAbstract $comment)
    {
        $object     = $comment->getObject();
        $objectType = $comment->getObjectType();
        $loginLink  = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentNewComment::class, [$comment, $object, $objectType, $loginLink]);
    }

    /**
     * @param Feedback $feedback
     *
     * @return AgentNewFeedback
     */
    public function createAgentNewFeedbackModel(Feedback $feedback)
    {
        $person    = $feedback->getPerson();
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentNewFeedback::class, [$feedback, $person, $loginLink]);
    }

    /**
     * @param Person $person
     *
     * @return AgentNewRegistration
     */
    public function createAgentNewRegistrationModel(Person $person)
    {
        return $this->convertParameters(AgentNewRegistration::class, [$person]);
    }

    /**
     * @param Person $performer
     * @param $newPassword
     *
     * @return AgentPasswordResetAlert
     */
    public function createAgentPasswordResetAlertModel(Person $performer, $newPassword)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentPasswordResetAlert::class, [$performer, $newPassword, $loginLink]);
    }

    /**
     * @param Person|Task $task
     * @param Person      $performer
     *
     * @return AgentTaskAssigned
     */
    public function createAgentTaskAssignedModel(Task $task, Person $performer)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentTaskAssigned::class, [$task, $performer, $loginLink]);
    }

    /**
     * @param Person|Task $task
     * @param Person      $performer
     *
     * @return AgentTaskAssigned
     */
    public function createAgentTaskCompletedModel(Task $task, Person $performer)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentTaskCompleted::class, [$task, $performer, $loginLink]);
    }

    /**
     * @param Person|Task $task
     * @param Person      $performer
     *
     * @return AgentTaskDueReminder
     */
    public function createAgentTaskDueReminderModel(Task $task, Person $performer)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentTaskDueReminder::class, [$task, $performer, $loginLink]);
    }

    /**
     * @param string $agentPassword
     *
     * @return AgentWelcome
     */
    public function createAgentWelcomeModel($agentPassword)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentWelcome::class, [$agentPassword, $loginLink]);
    }

    /**
     * @param string $agentPassword
     *
     * @return AgentWelcomeUsersource
     */
    public function createAgentWelcomeUsersourceModel($agentPassword)
    {
        return $this->convertParameters(AgentWelcomeUsersource::class, [$this->router, $agentPassword]);
    }

    /**
     * @param string $url
     *
     * @return AgentWhitelistIp
     */
    public function createAgentWhitelistIpModel($url)
    {
        return $this->convertParameters(AgentWhitelistIp::class, [$url]);
    }

    /**
     * @param DateTime $firstSeen
     * @param bool     $success
     * @param Request  $request
     *
     * @return AgentLoginAlert
     */
    public function createAgentLoginAlertModel(Request $request, DateTime $firstSeen, $success)
    {
        $firstSeen           = $firstSeen->format('D, jS M Y g:ia');
        $clientIp            = $request->getClientIp();
        $clientUserAgent     = $request->headers->get('User-Agent');
        $clientLandingPage   = $request->getRequestUri();
        $clientReferringPage = $request->headers->get('Referer');

        $arguments = [$firstSeen, $clientIp, $clientUserAgent, $clientLandingPage, $clientReferringPage, $success];

        return $this->convertParameters(AgentLoginAlert::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return AgentTicketNew
     */
    public function createAgentTicketNewModel(Ticket $ticket)
    {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(AgentTicketNew::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return AgentTicketUpdate
     */
    public function createAgentTicketUpdateModel(Ticket $ticket)
    {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(AgentTicketUpdate::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return AgentTicketReply
     */
    public function createAgentTicketReplyModel(Ticket $ticket)
    {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(AgentTicketReply::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     * @param string $agentMessage
     * @param string $subject
     *
     * @return AgentTicketForward
     */
    public function createAgentTicketForwardModel(
        Ticket $ticket,
        $agentMessage,
        $subject
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $arguments = array_merge($arguments, [$agentMessage, $subject]);

        return $this->convertParameters(AgentTicketForward::class, $arguments);
    }

    public function getTicketArguments($ticket)
    {
        $arguments = parent::getTicketArguments($ticket);

        $department = $ticket->getDepartment();
        $layoutId   = $department ? $department->getId() : null;
        $layout     = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($layoutId);
        $layout     = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);

        array_push($arguments, $ticket->getParticipants(), $layout);

        return $arguments;
    }
}
