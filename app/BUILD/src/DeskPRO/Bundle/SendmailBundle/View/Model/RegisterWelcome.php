<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class RegisterWelcome extends UserEmailBaseType
{
    protected $templateFile = 'emails_user:register_welcome.html.twig';
}
