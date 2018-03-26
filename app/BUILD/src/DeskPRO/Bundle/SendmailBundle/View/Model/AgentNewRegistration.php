<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;

class AgentNewRegistration extends EmailBaseType
{
    /**
     * Person that registered.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $person;

    protected $templateFile = 'emails_agent:new_registration.html.twig';

    public function __construct(Person $person)
    {
        $this->person = $person;
    }
}
