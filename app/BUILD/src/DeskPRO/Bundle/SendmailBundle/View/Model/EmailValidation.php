<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class EmailValidation extends EmailBaseType
{
    /**
     * A link to verify the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $verifyUrl;

    protected $templateFile = 'emails_user:email_validation.html.twig';

    /**
     * EmailValidation constructor.
     *
     * @param string $verifyUrl
     */
    public function __construct($verifyUrl)
    {
        $this->verifyUrl = $verifyUrl;
    }
}
