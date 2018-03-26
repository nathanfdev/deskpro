<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use JMS\Serializer\Annotation as JMS;

class EmailTooBig extends EmailBaseType
{
    /**
     * Email subject.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $subject;

    /**
     * Max size.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $maxSize;

    protected $templateFile = 'emails_user:email_too_big.html.twig';

    public function __construct($subject, $maxSize)
    {
        $this->subject = $subject;
        $this->maxSize = $maxSize;
    }
}
