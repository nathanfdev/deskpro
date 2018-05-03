<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class NewEmailValidatePrimary extends EmailBaseType
{
    /**
     * A link to verify the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $verifyUrl;

    protected $templateFile = 'emails_user:new_email_validate_primary.html.twig';

    /**
     * NewEmailValidatePrimary constructor.
     *
     * @param string $verifyUrl
     */
    public function __construct($verifyUrl)
    {
        $this->verifyUrl = $verifyUrl;
    }
}
