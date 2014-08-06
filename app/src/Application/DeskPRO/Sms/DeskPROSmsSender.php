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
 */

namespace Application\DeskPRO\Sms;

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
	protected $max_chunks;

	/**
	 * @param SmsProviderInterface $provider
	 * @param null                 $from_number
	 * @param null                 $max_chunks if a message requires more than this amount of messages to be send
	 *                                         it will fail and not send any
	 */
	public function __construct(SmsProviderInterface $provider = null, $from_number = null, $max_chunks = null)
	{
		$this->default_provider = $provider;
		$this->from_number      = $from_number;
		$this->max_chunks       = $max_chunks;
	}


	/**
	 * Same as the Orb SmsSender, except DeskPRO can fail a message if it exceeds a set max chunks
	 *
	 * @param string               $to_number
	 * @param SmsMessage           $message
	 * @param null                 $from_number
	 * @param SmsProviderInterface $provider
	 */
	public function send($to_number, SmsMessage $message, $from_number = null, SmsProviderInterface $provider = null)
	{
		if ($message->hasMultipleChunks() && count($message->getChunks()) > $this->max_chunks) {
			throw new SmsException(sprintf('the message contains too many chunks (%s chunks, but the system limit
			for SMS chunks is %s', count($message->getChunks()), $this->max_chunks));
		}

		$this->doSend($to_number, $message, $from_number, $provider);
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
