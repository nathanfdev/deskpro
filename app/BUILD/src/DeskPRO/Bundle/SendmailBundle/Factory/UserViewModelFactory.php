<?php

namespace DeskPRO\Bundle\SendmailBundle\Factory;

use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\ChatMessage;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Download;
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\FeedbackComment;
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use DateTime;
use DeskPRO\Bundle\SendmailBundle\View\Model\AccountDisabled;
use DeskPRO\Bundle\SendmailBundle\View\Model\AgentChangedPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\ChatTranscript;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentDeleted;
use DeskPRO\Bundle\SendmailBundle\View\Model\CommentNew;
use DeskPRO\Bundle\SendmailBundle\View\Model\DownloadSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailTooBig;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailValidation;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackApproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackCreatedForUser;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackDisapproved;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackNew;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackNewComment;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\FeedbackUpdated;
use DeskPRO\Bundle\SendmailBundle\View\Model\GatewayAutoresponseWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\KbSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\LoginAlert;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewEmailValidate;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewEmailValidatePrimary;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewReplyRejectResolved;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewsSubscription;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketGuest;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketRegClosed;
use DeskPRO\Bundle\SendmailBundle\View\Model\NewTicketValidate;
use DeskPRO\Bundle\SendmailBundle\View\Model\RegisterWelcome;
use DeskPRO\Bundle\SendmailBundle\View\Model\RegisterWelcomeByAgent;
use DeskPRO\Bundle\SendmailBundle\View\Model\ResetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\SetPassword;
use DeskPRO\Bundle\SendmailBundle\View\Model\ShareArticle;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAddCc;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAutocloseWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarn;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketAwaitingWarnFinal;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewByAgent;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewReminder;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketNewValidateEmail;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketParticipant;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketRate;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyAutoreply;
use DeskPRO\Bundle\SendmailBundle\View\Model\TicketReplyByAgent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserViewModelFactory extends AbstractViewModelFactory
{
    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     *
     * @return AccountDisabled
     */
    public function createAccountDisabledModel(Ticket $ticket)
    {
        return $this->convertParameters(AccountDisabled::class, [$ticket]);
    }

    /**
     * @param string $newPassword
     *
     * @return AgentChangedPassword
     */
    public function createAgentChangedPasswordModel($newPassword)
    {
        $userLink = $this->router->generate('user', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(AgentChangedPassword::class, [$userLink, $newPassword]);
    }

    /**
     * @param ChatConversation $chat
     * @param ChatMessage[]    $convoMessages
     *
     * @return ChatTranscript
     */
    public function createChatTranscriptModel(ChatConversation $chat, array $convoMessages)
    {
        return $this->convertParameters(ChatTranscript::class, [$chat, $convoMessages]);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentApproved
     */
    public function createCommentApprovedModel(CommentAbstract $comment)
    {
        $content     = $comment->getObject();
        $contentLink = $this->objectRouter->getPortalUrl($content);

        return $this->convertParameters(CommentApproved::class, [$comment, $content, $contentLink]);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentDeleted
     */
    public function createCommentDeletedModel(CommentAbstract $comment)
    {
        $content     = $comment->getObject();
        $contentLink = $this->objectRouter->getPortalUrl($content);

        return $this->convertParameters(CommentDeleted::class, [$comment, $content, $contentLink]);
    }

    /**
     * @param CommentAbstract $comment
     *
     * @return CommentNew
     */
    public function createCommentNewModel(CommentAbstract $comment)
    {
        $content     = $comment->getObject();
        $contentLink = $this->objectRouter->getPortalUrl($content);

        return $this->convertParameters(CommentNew::class, [$comment, $content, $contentLink]);
    }

    /**
     * @param Download[] $newDownloads
     * @param Download[] $updatedDownloads
     *
     * @return DownloadSubscription
     */
    public function createDownloadSubscriptionModel(array $newDownloads, array $updatedDownloads)
    {
        $portalHome     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $unsubscribeUrl = $this->router->generate('portal_downloads_unsubscribe_all', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(DownloadSubscription::class, [$portalHome, $unsubscribeUrl, $newDownloads, $updatedDownloads]);
    }

    /**
     * @param string $verifyUrl
     *
     * @return EmailValidation
     */
    public function createEmailValidationModel($verifyUrl)
    {
        return $this->convertParameters(EmailValidation::class, [$verifyUrl]);
    }

    /**
     * @param Feedback $feedback
     * @param Person   $agent
     *
     * @return FeedbackApproved
     */
    public function createFeedbackApprovedModel(Feedback $feedback, Person $agent)
    {
        $feedbackLink = $this->router->generate('user_feedback_view', ['slug' => $feedback->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(FeedbackApproved::class, [$feedback, $agent, $feedbackLink]);
    }

    /**
     * @param Feedback $feedback
     * @param Person   $agent
     * @param string   $reason
     *
     * @return FeedbackDisapproved
     */
    public function createFeedbackDisapprovedModel(Feedback $feedback, Person $agent, $reason)
    {
        return $this->convertParameters(FeedbackDisapproved::class, [$feedback, $agent, $reason]);
    }

    /**
     * @param Feedback $feedback
     *
     * @return FeedbackNew
     */
    public function createFeedbackNewModel(Feedback $feedback)
    {
        return $this->convertParameters(FeedbackNew::class, [$feedback]);
    }

    /**
     * @param FeedbackComment $comment
     *
     * @return FeedbackNewComment
     */
    public function createFeedbackNewCommentModel(FeedbackComment $comment)
    {
        $feedback     = $comment->getFeedback();
        $feedbackLink = $this->router->generate('user_feedback_view', ['slug' => $feedback->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(FeedbackNewComment::class, [$comment, $feedback, $feedbackLink]);
    }

    public function createFeedbackUpdatedModel(Feedback $feedback)
    {
        $feedbackLink = $this->router->generate('user_feedback_view', ['slug' => $feedback->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(FeedbackUpdated::class, [$feedback, $feedbackLink]);
    }

    /**
     * @param Feedback[] $updatedFeedback
     *
     * @return FeedbackSubscription
     */
    public function createFeedbackSubscriptionModel(array $updatedFeedback)
    {
        $portalHome     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $unsubscribeUrl = $this->router->generate('portal_feedback_unsubscribe_all', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(FeedbackSubscription::class, [$portalHome, $unsubscribeUrl, $updatedFeedback]);
    }

    /**
     * @param Feedback $feedback
     *
     * @return FeedbackCreatedForUser
     */
    public function createFeedbackCreatedForUserModel(Feedback $feedback)
    {
        return $this->convertParameters(FeedbackCreatedForUser::class, [$feedback]);
    }

    /**
     * @param Article[] $newArticles
     * @param Article[] $updatedArticles
     *
     * @return KbSubscription
     */
    public function createKbSubscriptionModel(array $newArticles, array $updatedArticles)
    {
        $portalHome     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $unsubscribeUrl = $this->router->generate('portal_kb_unsubscribe_all', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(KbSubscription::class, [$portalHome, $unsubscribeUrl, $newArticles, $updatedArticles]);
    }

    /**
     * @param Request  $request
     * @param DateTime $firstSeen
     * @param bool     $success
     *
     * @return LoginAlert
     */
    public function createLoginAlertModel(Request $request, DateTime $firstSeen, $success)
    {
        $clientIp            = $request->getClientIp();
        $clientUserAgent     = $request->headers->get('User-Agent');
        $clientLandingPage   = $request->getRequestUri();
        $clientReferringPage = $request->headers->get('Referer');
        $firstSeen           = $firstSeen->format('D, jS M Y g:ia');

        return $this->convertParameters(
            LoginAlert::class,
            [
                $clientIp,
                $clientUserAgent,
                $clientLandingPage,
                $clientReferringPage,
                $firstSeen,
                $success,
            ]);
    }

    /**
     * @param string $verifyUrl
     * @param string $origEmail
     * @param string $newEmail
     *
     * @return NewEmailValidate
     */
    public function createNewEmailValidateModel($verifyUrl, $origEmail, $newEmail)
    {
        return $this->convertParameters(NewEmailValidate::class, [$verifyUrl, $origEmail, $newEmail]);
    }

    /**
     * @param string $verifyUrl
     *
     * @return NewEmailValidatePrimary
     */
    public function createNewEmailValidatePrimaryModel($verifyUrl)
    {
        return $this->convertParameters(NewEmailValidatePrimary::class, [$verifyUrl]);
    }

    /**
     * @param Ticket $ticket
     *
     * @return NewReplyRejectResolved
     */
    public function createNewReplyRejectResolvedModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(NewReplyRejectResolved::class, $arguments);
    }

    /**
     * @param News[] $newNews
     * @param News[] $updatedNews
     *
     * @return NewsSubscription
     */
    public function createNewsSubscriptionModel(array $newNews, array $updatedNews)
    {
        $portalHome     = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $unsubscribeUrl = $this->router->generate('portal_news_unsubscribe_all', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(NewsSubscription::class, [$portalHome, $unsubscribeUrl, $newNews, $updatedNews]);
    }

    /**
     * @param Ticket $ticket
     *
     * @throws \Exception
     *
     * @return NewTicketGuest
     */
    public function createNewTicketGuestModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(NewTicketGuest::class, $arguments);
    }

    /**
     * @param string $subject
     *
     * @throws \Exception
     *
     * @return NewTicketRegClosed
     */
    public function createNewTicketRegClosedModel($subject)
    {
        return new NewTicketRegClosed($subject);
    }

    /**
     * @param Ticket $ticket
     * @param string $accessCode
     *
     * @return NewTicketValidate
     */
    public function createNewTicketValidateModel(
        Ticket $ticket,
        $accessCode
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $arguments = array_merge($arguments, [$accessCode]);

        return $this->convertParameters(NewTicketValidate::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     * @param string $verifyUrl
     *
     * @return TicketNewValidateEmail
     */
    public function createTicketNewValidateEmailModel(
        Ticket $ticket,
        $verifyUrl
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $arguments = array_merge($arguments, [$verifyUrl]);

        return $this->convertParameters(TicketNewValidateEmail::class, $arguments);
    }

    /**
     * @return RegisterWelcome
     */
    public function createRegisterWelcomeModel()
    {
        $portalHome = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(RegisterWelcome::class, [$portalHome]);
    }

    /**
     * @param string $newPassword
     *
     * @return RegisterWelcomeByAgent
     */
    public function createRegisterWelcomeByAgentModel($newPassword)
    {
        $portalHome = $this->router->generate('portal_home', [], UrlGeneratorInterface::ABSOLUTE_URL);

        return $this->convertParameters(RegisterWelcomeByAgent::class, [$portalHome, $newPassword]);
    }

    /**
     * @param string $resetUrl
     *
     * @return ResetPassword
     */
    public function createResetPasswordModel($resetUrl)
    {
        return $this->convertParameters(ResetPassword::class, [$resetUrl]);
    }

    /**
     * @param string $resetUrl
     *
     * @return SetPassword
     */
    public function createSetPasswordModel($resetUrl)
    {
        return $this->convertParameters(SetPassword::class, [$resetUrl]);
    }

    /**
     * @param Article $article
     * @param Person  $author
     * @param string  $message
     * @param string  $email
     * @param string  $name
     *
     * @return ShareArticle
     */
    public function createShareArticleModel(
        Article $article,
        Person $author,
        $message,
        $email,
        $name
    ) {
        $articleLink = $this->objectRouter->getPortalUrl($article);

        return $this->convertParameters(ShareArticle::class, [$article, $articleLink, $author, $message, $email, $name]);
    }

    /**
     * @param Ticket $ticket
     * @param Person $author
     *
     * @return TicketAddCc
     */
    public function createTicketAddCcModel(
        Ticket $ticket,
        Person $author
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $arguments = array_merge($arguments, [$author]);

        return $this->convertParameters(TicketAddCc::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAutocloseWarn
     */
    public function createTicketAutocloseWarnModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketAutocloseWarn::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarn
     */
    public function createTicketAwaitingWarnModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $ticketResolveLink = $this->objectRouter->getPortalUrl($ticket, 'resolve');
        $arguments         = array_merge($arguments, [$ticketResolveLink]);

        return $this->convertParameters(TicketAwaitingWarn::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketAwaitingWarnFinal
     */
    public function createTicketAwaitingWarnFinalModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $ticketResolveLink = $this->objectRouter->getPortalUrl($ticket, 'resolve');
        $arguments         = array_merge($arguments, [$ticketResolveLink]);

        return $this->convertParameters(TicketAwaitingWarnFinal::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewAutoreply
     */
    public function createTicketNewAutoreplyModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketNewAutoreply::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketNewByAgent
     */
    public function createTicketNewByAgentModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketNewByAgent::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketParticipant
     */
    public function createTicketParticipantModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketParticipant::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketRate
     */
    public function createTicketRateModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketRate::class, $arguments);
    }

    /**
     * @param Ticket        $ticket
     * @param TicketMessage $message
     *
     * @return TicketReplyByAgent
     */
    public function createTicketReplyByAgentModel(
        Ticket $ticket,
        TicketMessage $message
    ) {
        $arguments = $this->getTicketArguments($ticket);

        $showRatingLink = false;
        if ($this->container->get('settings_resolver')->getGlobalSettings()->get('core_tickets.enable_feedback') && $message && $message->getPerson()->isAgent() && !$message->isAgentNote()) {
            $showRatingLink = true;
        }

        $arguments = array_merge($arguments, [$message, $showRatingLink]);

        return $this->convertParameters(TicketReplyByAgent::class, $arguments);
    }

    /**
     * @param Ticket $ticket
     *
     * @return TicketReplyAutoreply
     */
    public function createTicketReplyAutoreplyModel(
        Ticket $ticket
    ) {
        $arguments = $this->getTicketArguments($ticket);

        return $this->convertParameters(TicketReplyAutoreply::class, $arguments);
    }

    /**
     * @param $verifyUrl
     * @param $expireDate
     *
     * @throws \Exception
     *
     * @return TicketNewReminder
     */
    public function createTicketNewReminderModel($verifyUrl, $expireDate)
    {
        $expireDate = $expireDate->format('D, jS M Y g:ia');

        return $this->convertParameters(TicketNewReminder::class, [$verifyUrl, $expireDate]);
    }

    /**
     * @return GatewayAutoresponseWarn
     */
    public function createGatewayAutoresponseWarnModel()
    {
        return $this->convertParameters(GatewayAutoresponseWarn::class, []);
    }

    /**
     * @param string $subject
     * @param string $maxSize
     *
     * @return EmailTooBig
     */
    public function createEmailTooBigModel($subject, $maxSize)
    {
        return $this->convertParameters(EmailTooBig::class, [$subject, $maxSize]);
    }
}
