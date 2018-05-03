<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Notifications;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\Person;

class NewCommentNotification extends AbstractAgentNotification
{
    /**
     * @var \Application\DeskPRO\Entity\CommentAbstract
     */
    protected $comment;

    public function __construct(CommentAbstract $comment)
    {
        parent::__construct();
        $this->comment = $comment;
    }

    public function shouldSendBrowserNotification(Person $person)
    {
        if ($this->comment->getId()) {
            if ($this->comment->isReviewed() && $person->getPref('agent_notif.new_comment_validate.alert')) {
                return true;
            } elseif (!$this->comment->isReviewed() && $person->getPref('agent_notif.new_comment.alert')) {
                return true;
            }
        }

        return false;
    }

    public function shouldSendEmailNotification(Person $person)
    {
        if ($this->comment->getId()) {
            if ($this->comment->isReviewed() && $person->getPref('agent_notif.new_comment_validate.email')) {
                return true;
            } elseif (!$this->comment->isReviewed() && $person->getPref('agent_notif.new_comment.email')) {
                return true;
            }
        }

        return false;
    }

    public function send()
    {
        $this->sendBrowserNotifications('AgentBundle:Publish:alert-new-comment.html.twig', ['comment' => $this->comment, 'notify_data' => ['notify_type' => 'new_comment']]);
        if (App::$container->get('deskpro.feature_flags')->hasBeta('email_templates')) {
            $viewModel = App::$container->get('email.agent_viewmodel_factory')
                ->createAgentNewCommentModel($this->comment);
            $this->sendNewEmailNotifications($viewModel);
        } else {
            $this->sendEmailNotifications('DeskPRO:emails_agent:new-comment.html.twig', ['comment' => $this->comment]);
        }
    }
}
