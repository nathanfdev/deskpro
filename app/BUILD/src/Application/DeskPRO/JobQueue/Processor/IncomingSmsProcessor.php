<?php

namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\Entity\TicketSms;
use Application\DeskPRO\Sms\Detector\PersonDetector;
use Application\DeskPRO\Sms\Detector\SmsAccountDetector;
use Application\DeskPRO\Sms\Detector\TicketDetector;
use Application\DeskPRO\Tickets\ExecutorContext;
use Application\DeskPRO\Tickets\TicketManager;
use Doctrine\DBAL\Connection;
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
    const JOB_TYPE               = 'incoming_sms';
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
     * @param Connection         $connection
     * @param SmsAccountDetector $sms_account_detector
     * @param PersonDetector     $person_detector
     * @param TicketDetector     $ticket_detector
     * @param TicketManager      $ticket_manager
     */
    public function __construct(
        Connection $connection,
        SmsAccountDetector $sms_account_detector,
        PersonDetector $person_detector,
        TicketDetector $ticket_detector,
        TicketManager $ticket_manager
    ) {
        parent::__construct($connection);
        $this->sms_account_detector = $sms_account_detector;
        $this->person_detector      = $person_detector;
        $this->ticket_detector      = $ticket_detector;
        $this->ticket_manager       = $ticket_manager;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['message', 'from_number']);

        $resolver->setDefaults(['sms_account_id' => null, 'to_number' => null]);
    }

    /**
     * {@inheritdoc}
     */
    public function process(array $data, array $job)
    {
        /************************************
         * Detect SMS Account
         */

        $sms_account_id = $data['sms_account_id'];
        $to_number      = $data['to_number'];
        $sms_account    = $this->sms_account_detector->detect($sms_account_id, $to_number);
        if (!$sms_account) {
            $this->markRejected($job, 'SMS Account not found', '', Job::STATUS_CODE_INVALID_DATA);

            return false;
        }

        /************************************
         * Who sent this text? Detect or create new Person
         */

        $from_number = $data['from_number'];
        if (!$from_person = $this->person_detector->detectWithFromNumber($from_number)) {
            $from_person = $this->person_detector->createPersonWithNumber($from_number);
        }

        // note: event type changed below
        $context = $this->ticket_manager->createSystemExecutorContext('', ExecutorContext::METHOD_SMS);
        $context->setPersonContext($from_person);

        /************************************
         * Detect an existing ticket to reply to, or create a new ticket
         */

        $ticket = $this->ticket_detector->detectBySender($from_person);
        if (!$ticket) {
            $context->setEventType(ExecutorContext::EVENT_NEW);
            $ticket                  = $this->ticket_manager->createTicket();
            $ticket->person          = $from_person;
            $ticket->subject         = '(no subject)';
            $ticket->creation_system = static::TICKET_CREATION_SYSTEM;
        } else {
            $context->setEventType(ExecutorContext::EVENT_REPLY);
        }

        /************************************
         * Create a new Ticket SMS Message
         */

        $message              = new TicketSms('incoming');
        $message->ticket      = $ticket;
        $message->person      = $from_person;
        $message->sms_account = $sms_account;
        $message->job_id      = $job['id'];
        $message->message     = $data['message'];
        $message->from_number = $from_number;
        $message->to_number   = $to_number;

        /************************************
         * Add the new message to our ticket
         */
        $ticket->addSmsMessage($message);

        // Done!
        $this->ticket_manager->saveTicket($ticket, $context);

        return true;
    }
}
