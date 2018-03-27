<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Ticket SMS Message.
 *
 *@property int $id
 * @property Ticket $ticket
 * @property Person $person
 * @property SmsAccount $sms_account
 * @property \Application\DeskPRO\Entity\Job $job
 * @property \DateTime $date_created
 * @property string $from_number
 * @property string $to_number
 * @property string $message
 * @property string $direction
 */
class TicketSms extends \Application\DeskPRO\Domain\DomainObject
{
    /**
     * @var int
     */
    protected $id = null;

    /**
     * @var \Application\DeskPRO\Entity\Ticket
     */
    protected $ticket = null;

    /**
     * @var \Application\DeskPRO\Entity\Person
     */
    protected $person = null;

    /**
     * @var \Application\DeskPRO\Entity\SmsAccount
     */
    protected $sms_account = null;

    /**
     * @var \Application\DeskPRO\Entity\Job
     */
    protected $job = null;

    /**
     * @var \DateTime
     */
    protected $date_created;

    /**
     * Permanent record of the phone number that sent this SMS. (Won't change even if Person changes their phone number).
     *
     * @var string
     */
    protected $from_number = '';

    /**
     * Permanent record of the phone number that this SMS was sent to.
     *
     * @var string
     */
    protected $to_number = '';

    /**
     * The SMS message.
     *
     * @var string
     */
    protected $message;

    /**
     * Just a system flag for reporting.
     *
     * @var string "incoming" or "outgoing"
     */
    protected $direction;

    public function __construct($direction)
    {
        $this->setModelField('direction', $direction);
        $this->setModelField('date_created', new \DateTime());
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setTicketId($id)
    {
        $this->setModelField('ticket', App::getEntityRepository('DeskPRO:Ticket')->find($id));
    }

    public function getTicketId()
    {
        return $this->ticket['id'];
    }

    public function setPersonId($id)
    {
        $this->setModelField('person', App::getEntityRepository('DeskPRO:Person')->find($id));
    }

    public function getPersonId()
    {
        return $this->person['id'];
    }

    public function setJobId($id)
    {
        $this->setModelField('job', App::getEntityRepository('DeskPRO:Job')->find($id));
    }

    public function incTicketCount()
    {
        if (!$this->ticket) {
            return;
        }

        if ($this->person->is_agent) {
            ++$this->ticket->count_agent_replies;
        } else {
            ++$this->ticket->count_user_replies;
        }
    }

    //###########################################################################
    // Doctrine Metadata
    //###########################################################################

    public static function loadMetadata(ClassMetadata $metadata)
    {
        $builder = new ClassMetadataBuilder($metadata);

        $builder->setTable('tickets_sms');
        $builder->addIndex(['date_created'], 'date_created_idx');
        $builder->setCustomRepositoryClass('Application\DeskPRO\EntityRepository\TicketSms');
        $builder->addLifecycleEvent('incTicketCount', 'prePersist');

        $builder->mapId();
        $builder->mapDateTime('date_created');
        $builder->mapString('from_number', 30);
        $builder->mapString('to_number', 30);
        $builder->mapString('direction', 10);
        $builder->mapText('message');

        $builder->addManyToOne('ticket', 'Application\\DeskPRO\\Entity\\Ticket', 'sms_messages');
        $builder->addManyToOne('person', 'Application\\DeskPRO\\Entity\\Person');
        $builder->addManyToOne('sms_account', 'Application\\DeskPRO\\Entity\\SmsAccount');
        $builder->addManyToOne('job', 'Application\\DeskPRO\\Entity\\Job');
    }
}
