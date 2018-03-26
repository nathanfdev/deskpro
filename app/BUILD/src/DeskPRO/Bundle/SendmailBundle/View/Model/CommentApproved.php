<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class CommentApproved extends CommentEmailType
{
    protected $templateFile = 'emails_user:comment_approved.html.twig';
}
