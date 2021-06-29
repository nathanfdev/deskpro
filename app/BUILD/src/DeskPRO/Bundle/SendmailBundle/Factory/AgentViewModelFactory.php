<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\CommunityTopic;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Task;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\DeskPRO\TicketLayout\LayoutDisplay;
use DateTime;
use DeskPRO\Bundle\AppBundle\Entity\AgentChatMessage;
use DeskPRO\Bundle\AppBundle\Entity\Approval\AbstractBaseApproval;
use DeskPRO\Bundle\AppBundle\Entity\Approval\ApprovalResponse;
use DeskPRO\Bundle\AppBundle\Entity\Approval\TicketApproval;
use DeskPRO\Bundle\SendmailBundle\View\Model\AdminNoResetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentChangeEmailMergeUser;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorInvalidForward;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorMarkerMissing;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentErrorUnknownFrom;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentLoginAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewChatMessage;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewComment;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewCommunityTopic;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentNewImMessage;
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
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverCancel;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverCreate;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverPartialApprovalResponse;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverPartialRejectionResponse;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalApproverRejected;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerCancel;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerCreate;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerPartialApprovalResponse;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerPartialRejectionResponse;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketApprovalOwnerRejected;
use DeskPRO\Bundle\SendmailBundle\View\TicketApprovalViewModelMapTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AgentViewModelFactory extends AbstractViewModelFactory
{
    /*
     * Use generic ticket approval event to view model map
     */
    use TicketApprovalViewModelMapTrait;

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
     * @param $error
     *
     * @return AgentErrorInvalidForward
     */
    public function createAgentErrorInvalidForwardModel($error)
    {
        return new AgentErrorInvalidForward($error);
    }

    /**
     * @param Ticket $ticket
     * @param $subject
     *
     * @throws \Exception
     *
     * @return AgentErrorMarkerMissing
     */
    public function createAgentErrorMarkerMissingModel(Ticket $ticket, $subject)
    {
        $arguments = $this->getTicketArguments($ticket);

        array_push($arguments, $subject);

        return $this->convertParameters(AgentErrorMarkerMissing::class, $arguments);
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
     * @param AgentChatMessage $chatMessage
     *
     * @return AgentNewChatMessage
     */
    public function createAgentNewImMessageModel(AgentChatMessage $chatMessage)
    {
        if ($chatMessage->getPerson()) {
            $author = $chatMessage->getPerson();
        } else {
            $author = null;
        }

        return $this->convertParameters(AgentNewImMessage::class, [$chatMessage, $author]);
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
     * @param CommunityTopic $communityTopic
     *
     * @return AgentNewCommunityTopic
     */
    public function createAgentNewCommunityTopicModel(CommunityTopic $communityTopic)
    {
        $person    = $communityTopic->getPerson();
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentNewCommunityTopic::class, [$communityTopic, $person, $loginLink]);
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
    public function createAgentTaskDueReminderModel(Task $task)
    {
        $loginLink = $this->router->generate('agent', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentTaskDueReminder::class, [$task, $loginLink]);
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
    public function createAgentWhitelistIpModel($url, $ip)
    {
        return $this->convertParameters(AgentWhitelistIp::class, [$url, $ip]);
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
     * @param Ticket          $ticket
     * @param string          $agentMessage
     * @param string          $subject
     * @param TicketMessage[] $ticketMessages
     * @param Blob[]          $attachments
     *
     * @throws \Exception
     *
     * @return AgentTicketForward
     */
    public function createAgentTicketForwardModel(
        Ticket $ticket,
        $agentMessage,
        $subject,
        $ticketMessages,
        $attachments = []
    ) {
        $arguments = $this->getTicketArguments($ticket, false, $ticketMessages);

        array_push($arguments, $agentMessage, $subject, $attachments);

        return $this->convertParameters(AgentTicketForward::class, $arguments);
    }

    public function getTicketArguments($ticket, $forAgent = true, $ticketMessages = null)
    {
        $arguments = parent::getTicketArguments($ticket, $forAgent, $ticketMessages);

        $department = $ticket->getDepartment();
        $layoutId   = $department ? $department->getId() : null;
        $layout     = $this->container->getTicketLayoutManager()->getAgentLayouts()->getLayout($layoutId);
        $layout     = LayoutDisplay::createFromLayout($layout, LayoutDisplay::VIEW_TICKET, $ticket);

        $customFields = $this->container->getTicketFieldManager()->getDisplayArrayForObject($ticket);

        $customUserFields = $this->container->getPersonFieldManager()->getDisplayArrayForObject($ticket->getPerson());

        array_push($arguments, $ticket->getParticipants(), $layout, $customFields, $customUserFields);

        return $arguments;
    }

    /**
     * @param string $event
     * @param Ticket $ticket
     * @param TicketApproval $approval
     * @param Person $recipient
     * @param bool $isOwner
     * @param $hasRecipientResponded
     * @param $approveUrl
     * @param $rejectUrl
     * @param ApprovalResponse|null $approvalResponse
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalModelByApprovalEvent(
        $event,
        Ticket $ticket,
        TicketApproval $approval,
        Person $recipient,
        $isOwner,
        $hasRecipientResponded,
        $approveUrl,
        $rejectUrl,
        ApprovalResponse $approvalResponse = null
    ) {
        return $this->convertParameters(
            $this->getViewModelByTicketApprovalEvent($event, $isOwner),
            [
                'agent',
                $ticket,
                $approval,
                $recipient,
                $isOwner,
                $hasRecipientResponded,
                $approveUrl,
                $rejectUrl,
                $approvalResponse,
                $approval->getResponses()->toArray(),
            ]
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverApprovedModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverApproved::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_APPROVED)
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverCancelModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverCancel::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_CANCELLED)
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverCreateModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverCreate::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'created')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverPartialApprovalResponseModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverPartialApprovalResponse::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'pending_approval')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverPartialRejectionResponseModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverPartialRejectionResponse::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'pending_rejection')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalApproverRejectedModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalApproverRejected::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_REJECTED)
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerApprovedModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerApproved::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_APPROVED)
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerCancelModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerCancel::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_CANCELLED)
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerCreateModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerCreate::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'created')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerPartialApprovalResponseModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerPartialApprovalResponse::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'pending_approval')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerPartialRejectionResponseModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerPartialRejectionResponse::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_PENDING, 'pending_rejection')
        );
    }

    /**
     * @param Ticket $ticket
     * @param Person $recipient
     *
     * @throws \Exception
     *
     * @return \DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType
     */
    public function createTicketApprovalOwnerRejectedModel(Ticket $ticket, Person $recipient)
    {
        return $this->convertParameters(
            TicketApprovalOwnerRejected::class,
            $this->getTicketApprovalArguments('agent', $ticket, $recipient, AbstractBaseApproval::STATUS_REJECTED)
        );
    }
}
