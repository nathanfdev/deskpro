<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class CommentDeleted extends CommentEmailType
{
    protected $templateFile = 'emails_user:comment_deleted.html.twig';
}
