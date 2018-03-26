<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class NewEmailValidate extends EmailBaseType
{
    /**
     * A link to the ticket.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $verifyUrl;

    /**
     * The original email.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $origEmail;

    /**
     * The new email to add.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $newEmail;

    protected $templateFile = 'emails_user:new_email_validate.html.twig';

    /**
     * EmailValidation constructor.
     *
     * @param string $verifyUrl
     * @param string $origEmail
     * @param string $newEmail
     */
    public function __construct($verifyUrl, $origEmail, $newEmail)
    {
        $this->verifyUrl = $verifyUrl;
        $this->origEmail = $origEmail;
        $this->newEmail  = $newEmail;
    }
}
