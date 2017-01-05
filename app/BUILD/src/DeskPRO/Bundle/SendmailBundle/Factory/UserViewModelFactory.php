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

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DeskPRO\Bundle\AppBundle\ObjectRouter\ObjectRouter;
use DeskPRO\Bundle\SendmailBundle\View\Model\AccountDisabled;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentChangedPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\ChatTranscript;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentDeleted;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentNew;
use DeskPRO\Bundle\SendmailBundle\View\Model\DownloadSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailValidation;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackDisapproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackNew;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackNewComment;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\KbSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\LoginAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewEmailValidate;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewEmailValidatePrimary;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewReplyRejectResolved;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewsSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketGuest;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketRegClosed;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketValidate;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketValidateEmail;
use DeskPRO\Bundle\SendmailBundle\View\Model\RegisterWelcome;
use DeskPRO\Bundle\SendmailBundle\View\Model\RegisterWelcomeByAgent;
use DeskPRO\Bundle\SendmailBundle\View\Model\ResetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\SetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAddCc;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAutocloseWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarnFinal;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewByAgent;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketParticipant;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketRate;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyByAgent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

class UserViewModelFactory
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
     * @return AccountDisabled
     */
    public function createAccountDisabledModel()
    {
        return new AccountDisabled();
    }

    /**
     * @param string $newPassword
     *
     * @return AgentChangedPassword
     */
    public function createAgentChangedPasswordModel($newPassword)
    {
        return new AgentChangedPassword($this->router, $newPassword);
    }

    /**
     * @param ChatConversation $chat
     * @param ChatMessage[]    $convoMessages
     *
     * @return ChatTranscript
     */
    public function createChatTranscriptModel($chat, $convoMessages)
    {
        return new ChatTranscript($chat, $convoMessages);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentApproved
     */
    public function createCommentApprovedModel($comment)
    {
        return new CommentApproved($this->objectRouter, $comment);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentDeleted
     */
    public function createCommentDeletedModel($comment)
    {
        return new CommentDeleted($this->objectRouter, $comment);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentNew
     */
    public function createCommentNewModel($comment)
    {
        return new CommentNew($this->objectRouter, $comment);
    }

    /**
     * @param Download[] $newDownloads
     * @param Download[] $updatedDownloads
     *
     * @return DownloadSubscription
     */
    public function createDownloadSubscriptionModel($newDownloads, $updatedDownloads)
    {
        return new DownloadSubscription($this->router, $newDownloads, $updatedDownloads);
    }

    /**
     * @param string $verifyUrl
     *
     * @return EmailValidation
     */
    public function createEmailValidationModel($verifyUrl)
    {
        return new EmailValidation($verifyUrl);
    }

    /**
     * @param Feedback $feedback
     * @param Person   $agent
     *
     * @return FeedbackApproved
     */
    public function createFeedbackApprovedModel($feedback, $agent)
    {
        return new FeedbackApproved($this->router, $feedback, $agent);
    }

    /**
     * @param Feedback $feedback
     * @param Person   $agent
     * @param string   $reason
     *
     * @return FeedbackDisapproved
     */
    public function createFeedbackDisapprovedModel($feedback, $agent, $reason)
    {
        return new FeedbackDisapproved($feedback, $agent, $reason);
    }

    /**
     * @param Feedback $feedback
     *
     * @return FeedbackNew
     */
    public function createFeedbackNewModel($feedback)
    {
        return new FeedbackNew($feedback);
    }

    /**
     * @param Feedback $feedback
     *
     * @return FeedbackNewComment
     */
    public function createFeedbackNewCommentModel($feedback)
    {
        return new FeedbackNewComment($feedback);
    }

    /**
     * @param Feedback[] $updatedItems
     *
     * @return FeedbackSubscription
     */
    public function createFeedbackSubscriptionModel($updatedItems)
    {
        return new FeedbackSubscription($this->router, $updatedItems);
    }

    /**
     * @param Article[] $newArticles
     * @param Article[] $updatedArticles
     *
     * @return KbSubscription
     */
    public function createKbSubscriptionModel($newArticles, $updatedArticles)
    {
        return new KbSubscription($this->router, $newArticles, $updatedArticles);
    }

    /**
     * @param string  $firstSeen
     * @param bool    $success
     * @param Request $request
     *
     * @return LoginAlert
     */
    public function createLoginAlertModel(Request $request, $firstSeen, $success)
    {
        return new LoginAlert($request, $firstSeen, $success);
    }

    /**
     * @param string $verifyUrl
     *
     * @return NewEmailValidate
     */
    public function createNewEmailValidateModel($verifyUrl)
    {
        return new NewEmailValidate($verifyUrl);
    }

    /**
     * @param string $verifyUrl
     *
     * @return NewEmailValidatePrimary
     */
    public function createNewEmailValidatePrimaryModel($verifyUrl)
    {
        return new NewEmailValidatePrimary($verifyUrl);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewReplyRejectResolved
     */
    public function createNewReplyRejectResolvedModel(
        $ticket
    ) {
        return new NewReplyRejectResolved($this->objectRouter, $ticket);
    }

    /**
     * @param News[] $newNews
     * @param News[] $updatedNews
     *
     * @return NewsSubscription
     */
    public function createNewsSubscriptionModel($newNews, $updatedNews)
    {
        return new NewsSubscription($this->router, $newNews, $updatedNews);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketGuest
     */
    public function createNewTicketGuestModel(
        $ticket
    ) {
        return new NewTicketGuest($this->objectRouter, $ticket);
    }

    /**
     * @return NewTicketRegClosed
     */
    public function createNewTicketRegClosedModel()
    {
        return new NewTicketRegClosed();
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketValidate
     */
    public function createNewTicketValidateModel(
        $ticket
    ) {
        return new NewTicketValidate($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewTicketValidateEmail
     */
    public function createNewTicketValidateEmailModel(
        $ticket
    ) {
        return new NewTicketValidateEmail($this->objectRouter, $ticket);
    }

    /**
     * @return RegisterWelcome
     */
    public function createRegisterWelcomeModel()
    {
        return new RegisterWelcome();
    }

    /**
     * @return RegisterWelcomeByAgent
     */
    public function createRegisterWelcomeByAgentModel()
    {
        return new RegisterWelcomeByAgent();
    }

    /**
     * @param string $resetUrl
     *
     * @return ResetPassword
     */
    public function createResetPasswordModel($resetUrl)
    {
        return new ResetPassword($resetUrl);
    }

    /**
     * @param string $resetUrl
     *
     * @return SetPassword
     */
    public function createSetPasswordModel($resetUrl)
    {
        return new SetPassword($resetUrl);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAddCc
     */
    public function createTicketAddCcModel(
        $ticket
    ) {
        return new TicketAddCc($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAutocloseWarn
     */
    public function createTicketAutocloseWarnModel(
        $ticket
    ) {
        return new TicketAutocloseWarn($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarn
     */
    public function createTicketAwaitingWarnModel(
        $ticket
    ) {
        return new TicketAwaitingWarn($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarnFinal
     */
    public function createTicketAwaitingWarnFinalModel(
        $ticket
    ) {
        return new TicketAwaitingWarnFinal($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewAutoreply
     */
    public function createTicketNewAutoreplyModel(
        $ticket
    ) {
        return new TicketNewAutoreply($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewByAgent
     */
    public function createTicketNewByAgentModel(
        $ticket
    ) {
        return new TicketNewByAgent($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketParticipant
     */
    public function createTicketParticipantModel(
        $ticket
    ) {
        return new TicketParticipant($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketRate
     */
    public function createTicketRateModel(
        $ticket
    ) {
        return new TicketRate($this->objectRouter, $ticket);
    }

    /**
     * @param Ticket        $ticket
     * @param TicketMessage $message
     *
     * @return TicketReplyByAgent
     */
    public function createTicketReplyByAgentModel(
        $ticket,
        $message
    ) {
        return new TicketReplyByAgent($this->objectRouter, $ticket, $message);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketReplyAutoreply
     */
    public function createTicketReplyAutoreplyModel(
        $ticket
    ) {
        return new TicketReplyAutoreply($this->objectRouter, $ticket);
    }
}
