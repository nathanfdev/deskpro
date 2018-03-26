<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class ResetPassword extends EmailBaseType
{
    /**
     * A link to reset your password.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $resetUrl;

    protected $templateFile = 'emails_user:reset_password.html.twig';

    /**
     * ResetPassword constructor.
     *
     * @param string $resetUrl
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }
}
