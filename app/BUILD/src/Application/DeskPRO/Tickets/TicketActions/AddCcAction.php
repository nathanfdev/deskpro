<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Tickets\TicketActions;

use Application\DeskPRO\App;
use Application\DeskPRO\EmailGateway\PersonFromEmailProcessor;
use Application\DeskPRO\EmailGateway\Reader\Item\EmailAddress;
use Application\DeskPRO\Entity\Ticket;
use Orb\Util\Arrays;
use Orb\Validator\StringEmail;

/**
 * Adds participants.
 */
class AddCcAction extends AbstractAction
{
    /**
     * @var string[]
     */
    protected $add_emails;

    /**
     * @var \Application\DeskPRO\Entity\Person[]
     */
    protected $add_people;

    public function __construct($add_emails)
    {
        if (!is_array($add_emails)) {
            $add_emails = explode(',', $add_emails);
            $add_emails = Arrays::func($add_emails, 'trim');
        }

        $valid = [];
        foreach ($add_emails as $email) {
            if (StringEmail::isValueValid($email)) {
                $valid[] = $email;
            }
        }

        $valid = array_unique($valid);

        $this->add_emails = $valid;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person[]
     */
    public function getPeople()
    {
        if ($this->add_people) {
            return $this->add_people;
        }

        $this->add_people = [];

        foreach ($this->add_emails as $email) {
            $person = App::getEntityRepository('DeskPRO:Person')->findOneByEmail($email);
            if ($person) {
                $this->add_people[$person->getId()] = $person;
            } else {
                if (App::getContainer()->getSetting('core.reg_enabled')) {
                    continue;
                }
                $person_processor = new PersonFromEmailProcessor();

                $eml        = new EmailAddress();
                $eml->email = $email;
                $person     = $person_processor->createPerson($eml);

                if ($person) {
                    $this->add_people[$person->getId()] = $person;
                }
            }
        }

        return $this->add_people;
    }

    /**
     * {@inheritdoc}
     */
    public function apply(Ticket $ticket)
    {
        $people = $this->getPeople();
        foreach ($people as $person) {
            $ticket->addParticipantPerson($person);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getApplyActions(Ticket $ticket)
    {
        $actions = [];

        foreach ($this->getPeople() as $pid => $person) {
            $actions[] = [
                'action'    => 'add_participant',
                'person_id' => $pid,
            ];
        }

        return $actions;
    }

    /**
     * @return string[]
     */
    public function getEmailAddresses()
    {
        return $this->add_emails;
    }

    /**
     * {@inheritdoc}
     */
    public function merge(ActionInterface $otherAction)
    {
        $email_addresses = $this->getEmailAddresses();
        $email_addresses = array_merge($email_addresses, $otherAction->getEmailAddresses());
        $email_addresses = array_unique($email_addresses);

        $new = new self($email_addresses);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription($as_html = true)
    {
        if (!$this->add_emails) {
            return '';
        }

        return 'CC users: '.implode(', ', $this->add_emails);
    }
}
