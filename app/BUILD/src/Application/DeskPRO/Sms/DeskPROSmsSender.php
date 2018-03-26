<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Sms;

use Application\DeskPRO\Entity\Job;
use Application\DeskPRO\JobQueue\JobQueue;
use Application\DeskPRO\JobQueue\Processor\OutgoingSmsProcessor;
use Orb\Sms\SmsException;
use Orb\Sms\SmsMessage;
use Orb\Sms\SmsProviderInterface;
use Orb\Sms\SmsSender;

/**
 * This is our app-specific version of the Orb package's SmsSender. Here we can do app-specific things like
 * $sms_sender->sendToAgentTeam($team, 'hello!'), etc.
 *
 * This is a compiled service in our container.
 */
class DeskPROSmsSender extends SmsSender
{
    /**
     * @var int|null
     */
    protected $max_chunks;

    /**
     * @var JobQueue
     */
    protected $queue;

    /**
     * @param SmsProviderInterface $provider
     * @param null                 $from_number
     * @param JobQueue             $queue
     * @param null                 $max_chunks  if a message requires more than this amount of messages to be send
     *                                          it will fail and not send any
     */
    public function __construct(SmsProviderInterface $provider = null, $from_number, JobQueue $queue, $max_chunks = null)
    {
        $this->default_provider = $provider;
        $this->from_number      = $from_number;
        $this->max_chunks       = $max_chunks;
        $this->queue            = $queue;
    }

    /**
     * Same as the Orb SmsSender, except DeskPRO can fail a message if it exceeds a set max chunks.
     *
     * @param string               $to_number
     * @param SmsMessage           $message
     * @param null                 $from_number
     * @param SmsProviderInterface $provider
     *
     * @return bool
     */
    public function send($to_number, SmsMessage $message, $from_number = null, SmsProviderInterface $provider = null)
    {
        if ($message->hasMultipleChunks() && count($message->getChunks()) > $this->max_chunks) {
            throw new SmsException(sprintf('the message contains too many chunks (%s chunks, but the system limit
            for SMS chunks is %s', count($message->getChunks()), $this->max_chunks));
        }

        return $this->doSend($to_number, $message, $from_number, $provider);
    }

    public function doSend($to_number, SmsMessage $message, $from_number = null, SmsProviderInterface $provider = null)
    {
        if (!$provider = $this->getProvider($provider)) {
            throw new SmsException('cannot send SMS without an SmsProvider');
        }

        $from_number = $this->getFromNumber($from_number);

        $data = [
            'provider'        => $provider->getName(),
            'provider_params' => $provider->getParams(),
            'to_number'       => $to_number,
            'from_number'     => $from_number,
            'message'         => $message->getRawMessage(),
        ];
        $job = new Job(
            OutgoingSmsProcessor::JOB_TYPE,
            $data
        );
        $this->queue->addJob($job);

        return true;
    }

    /**
     * @return int|null
     */
    public function getMaxChunks()
    {
        return $this->max_chunks;
    }

    /**
     * @param int|null $max_chunks
     */
    public function setMaxChunks($max_chunks)
    {
        $this->max_chunks = $max_chunks;
    }
}
