<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Orb\Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Ticket SMS Message

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
	 *
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
	 * Permanent record of the phone number that sent this SMS. (Won't change even if Person changes their phone number)
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
	 * The SMS message
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Just a system flag for reporting
	 *
	 * @var string "incoming" or "outgoing"
	 */
	protected $direction;

	public function __construct($direction)
	{
		$this->direction = $direction;
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
			$this->ticket->count_agent_replies++;
		} else {
			$this->ticket->count_user_replies++;
		}
	}


	############################################################################
	# Doctrine Metadata
	############################################################################

	public static function loadMetadata(ClassMetadata $metadata)
	{
		$builder = new ClassMetadataBuilder($metadata);

		$builder->setTable('tickets_sms');
		$builder->addIndex(array('date_created'), 'date_created_idx');
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
