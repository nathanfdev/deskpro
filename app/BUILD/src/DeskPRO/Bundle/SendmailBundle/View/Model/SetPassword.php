<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class SetPassword extends EmailBaseType
{
    /**
     * A link to reset your password.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $resetUrl;

    protected $templateFile = 'emails_user:set_password.html.twig';

    /**
     * SetPassword constructor.
     *
     * @param string $resetUrl
     */
    public function __construct($resetUrl)
    {
        $this->resetUrl = $resetUrl;
    }
}
