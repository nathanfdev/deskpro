<?php

/**
 * DeskPRO.
 */

namespace Application\AgentBundle\Form\Model;

class NewTicketPerson
{
    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var string */
    public $email_address;
    /** @var string ? */
    public $organization = 0;
    /** @var string */
    public $organization_position;
    /** @var int */
    public $language_id;
}
