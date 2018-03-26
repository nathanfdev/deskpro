<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Links participants to tickets.
 */
class TicketParticipant extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     *
     * @Assert\NotNull()
     */
    protected $ticket = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     *
     * @Assert\NotNull()
     */
    protected $person = null;

    /**
     * @var \Application\DeskPRO\Entity\TicketAccessCode
     */
    protected $access_code = null;

    /**
     * @var \Application\DeskPRO\Entity\PersonEmail
     *
     * @Assert\Valid()
     */
    protected $person_email = null;

    /**
     * Default checkbox status of the user.
     *
     * @var bool
     */
    protected $default_on = true;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param Person $person
     *
     * @return $this
     */
    public function setPerson(Person $person)
    {
        if ($this->person === $person) {
            return $this;
        }

        $this->setModelField('person', $person);

        if (!$this->person_email && $this->person->getPrimaryEmail()) {
            $this->setPersonEmail($this->person->getPrimaryEmail());
        }

        return $this;
    }

    /**
     * @return Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param Ticket $ticket
     *
     * @return $this
     */
    public function setTicket($ticket)
    {
        $this->setModelField('ticket', $ticket);

        return $this;
    }

    /**
     * @return Ticket
     */
    public function getTicket()
    {
        return $this->ticket;
    }

    /**
     * @param $id
     */
    public function setPersonId($id)
    {
        /** @var Person $person */
        $person = App::findEntity('DeskPRO:Person', $id);
        $this->setPerson($person);
    }

    /**
     * @param $id
     */
    public function setPersonEmailId($id)
    {
        $person_email         = App::findEntity('DeskPRO:PersonEmail', $id);
        $this['person_email'] = $person_email;
    }

    /**
     * @return PersonEmail
     */
    public function getPersonEmail()
    {
        return $this->person_email;
    }

    /**
     * @param PersonEmail $person_email
     *
     * @return $this
     */
    public function setPersonEmail(PersonEmail $person_email = null)
    {
        $this->setModelField('person_email', $person_email);

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailAddress()
    {
        return $this->person_email['email'];
    }

    public function _setAccessCode()
    {
        if (!$this->access_code) {
            // try to find an existing TAC for this person and ticket,
            // ie agents may already have one from them getting notifications

            $access_code = App::getEntityRepository('DeskPRO:TicketAccessCode')->findByTicketAndPerson(
                $this->ticket,
                $this->person
            );
            if (!$access_code) {
                $access_code = new TicketAccessCode();
            }

            $this['access_code'] = $access_code;
        }

        $this->access_code->person = $this->person;
        $this->access_code->ticket = $this->ticket;
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        if ($this->person_email && $this->person_email->getEmail()) {
            return $this->person_email->getEmail();
        }

        return '';
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $metadata->customRepositoryClassName = 'Application\DeskPRO\EntityRepository\Basic';
        $metadata->setInheritanceType(ClassMetadataInfo::INHERITANCE_TYPE_NONE);
        $metadata->setPrimaryTable(['name' => 'tickets_participants']);
        $metadata->setChangeTrackingPolicy(ClassMetadataInfo::CHANGETRACKING_NOTIFY);
        $metadata->addLifecycleCallback('_setAccessCode', 'prePersist');
        $metadata->mapField(
            [
                'fieldName'  => 'id',
                'type'       => 'integer',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'id',
                'id'         => true,
            ]
        );
        $metadata->mapField(
            [
                'fieldName'  => 'default_on',
                'type'       => 'boolean',
                'precision'  => 0,
                'scale'      => 0,
                'nullable'   => false,
                'columnName' => 'default_on',
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'ticket',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Ticket',
                'mappedBy'     => null,
                'inversedBy'   => 'participants',
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'ticket_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\Person',
                'mappedBy'     => null,
                'inversedBy'   => 'tickets',
                'cascade'      => ['persist'],
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => false,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
                'dpApi' => true,
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'access_code',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\TicketAccessCode',
                'cascade'      => ['persist', 'merge'],
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'access_code_id',
                        'referencedColumnName' => 'id',
                        'unique'               => false,
                        'nullable'             => true,
                        'onDelete'             => 'cascade',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->mapManyToOne(
            [
                'fieldName'    => 'person_email',
                'targetEntity' => 'Application\\DeskPRO\\Entity\\PersonEmail',
                'mappedBy'     => null,
                'inversedBy'   => null,
                'joinColumns'  => [
                    0 => [
                        'name'                 => 'person_email_id',
                        'referencedColumnName' => 'id',
                        'nullable'             => true,
                        'onDelete'             => 'set null',
                        'columnDefinition'     => null,
                    ],
                ],
            ]
        );
        $metadata->setIdGeneratorType(ClassMetadataInfo::GENERATOR_TYPE_IDENTITY);
    }
}
