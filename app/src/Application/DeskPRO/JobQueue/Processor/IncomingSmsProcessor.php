<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage
 */

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\PhoneNumber;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketSms;
use Application\DeskPRO\Sms\Detector\PersonDetector;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Application\DeskPRO\Sms\Detector\TicketDetector;
use Application\DeskPRO\Sms\SmsProviderFactory;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Orb\Sms\SmsException;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsSender;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Processes an incoming SMS message.
 *
 * The job data should be an array with the following keys:
 * - sms_account_id
 * - from_number
 * - to_number
 * - message
 *
 * This processor is unlikely to fail, and won't be retried if it does.
 */
class IncomingSmsProcessor extends AbstractJobProcessor
{
	const JOB_TYPE = 'incoming_sms';
	const TICKET_CREATION_SYSTEM = 'channel.sms.incoming';

	/**
	 * @var SmsAccountDetector
	 */
	private $sms_account_detector;

	/**
	 * @var PersonDetector
	 */
	private $person_detector;

	/**
	 * @var TicketDetector
	 */
	private $ticket_detector;

	/**
	 * @var TicketManager
	 */
	private $ticket_manager;


	/**
	 * @param Connection $connection
	 */
	public function __construct(
		Connection $connection,
		SmsAccountDetector $sms_account_detector,
		PersonDetector $person_detector,
		TicketDetector $ticket_detector,
		TicketManager $ticket_manager
	)
	{
		parent::__construct($connection);
		$this->sms_account_detector = $sms_account_detector;
		$this->person_detector = $person_detector;
		$this->ticket_detector = $ticket_detector;
		$this->ticket_manager = $ticket_manager;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getDataOptions()
	{
		$resolver = new OptionsResolver();

		$resolver->setRequired(array('message', 'from_number'));
		$resolver->setDefaults(array('sms_account_id' => null, 'to_number' => null));

		return $resolver;
	}

	/**
	 * {@inheritdoc}
	 */
	public function execute(array $job)
	{
		$this->touchJob($job);

		try {
			$data = $this->getData($job);
			$this->processSms($data, $job['id']);
			$this->markComplete($job, 'Incoming SMS Processed', '');

		} catch (\Exception $e) {

			$this->markExceptionError(
				$job,
				'failed',
				'Incoming SMS Processing Failed',
				$e
			);

		}
	}



	protected function processSms(array $data, $job_id)
	{
		$context = new ExecutorContext();
		$context->setEventMethod(ExecutorContext::METHOD_SMS);

		/************************************
		 * Detect SMS Account
		 */

		$sms_account_id = $data['sms_account_id'];
		$to_number = $data['to_number'];
		$sms_account = $this->sms_account_detector->detect($sms_account_id, $to_number);
		if (!$sms_account) {
			throw new \InvalidArgumentException("no sms account found");
		}


		/************************************
		 * Who sent this text? Detect or create new Person
		 */

		$from_number = $data['from_number'];
		if (!$from_person = $this->person_detector->detectWithFromNumber($from_number)) {
			$from_person = $this->person_detector->createPersonWithNumber($from_number);
		}
		$context->setPersonContext($from_person);


		/************************************
		 * Detect an existing ticket to reply to, or create a new ticket
		 */

		$ticket = $this->ticket_detector->detectBySender($from_person);
		if (!$ticket) {
			$context->setEventType(ExecutorContext::EVENT_NEW);
			$ticket = $this->ticket_manager->createTicket();
			$ticket->person = $from_person;
			$ticket->subject = '(no subject)';
			$ticket->creation_system = static::TICKET_CREATION_SYSTEM;
		} else {
			$context->setEventType(ExecutorContext::EVENT_REPLY);
		}


		/************************************
		 * Create a new Ticket SMS Message
		 */

		$message = new TicketSms();
		$message->ticket = $ticket;
		$message->person = $from_person;
		$message->sms_account = $sms_account;
		$message->job_id = $job_id;
		$message->message = $data['message'];
		$message->from_number = $from_number;
		$message->to_number = $to_number;


		/************************************
		 * Add the new message to our ticket
		 */
		$ticket->addSmsMessage($message);

		// Done!
		$this->ticket_manager->saveTicket($ticket, $context);
	}
}
