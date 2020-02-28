<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

class AdminNoResetPassword extends EmailBaseType
{
    use EventCodeEmailBaseType;

    protected $templateFile = 'emails_agent:admin_noreset_password.html.twig';

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return 'login';
    }
}
