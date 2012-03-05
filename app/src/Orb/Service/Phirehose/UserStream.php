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

namespace Orb\Service\Phirehose;

/**
 * Concrete Twitter API User Stream consuming class.
 *
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */
class UserStream extends \UserstreamPhirehose
{
	/**
	 * @var array
	 */
	protected $account;

	/**
	 * @var \Doctrine\DBAL\Connection
	 */
	protected $connection;

	/**
	 * @var array
	 */
	protected $log = array();

	/**
	 * Suppress Phirehose @error_log output.
	 *
	 * @param string $message
	 * @return void
	 */
	protected function log($message)
	{
		$this->log[] = $message;
	}

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->connection;
	}

	/**
	 * @param \Doctrine\DBAL\Connection
	 * @return void
	 */
	public function setConnection(\Doctrine\DBAL\Connection $connection)
	{
		$this->connection = $connection;
	}

	/**
	 * @return array
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * @param array $account
	 * @return void
	 */
	public function setAccount(array $account)
	{
		$this->account = $account;
	}

	/**
	 * Process raw streaming data.
	 *
	 * @param string $status
	 * @return void
	 */
	public function enqueueStatus($status)
	{
		// skip "ping -> pong"
		if (null === $status || !strlen(trim($status))) {
			return false;
		}

		// decode json
		$status = json_decode($status, true);
		$event = 'unknown';

		// check if status is a tweet
		if (isset($status['text'])) {
			$event = 'status';
		}

		// check direct message
		if (isset($status['direct_message'])) {
			$event = 'message';
		}

		// check event
		if (isset($status['event'])) {
			$event = 'event';
		}

		// check friend list
		if (isset($status['friends'])) {
			$event = 'friends';
		}

		if (isset($status['delete'])) {
			$event = 'delete';
		}

		$this->connection->insert('twitter_stream', array(
			'account_id' => $this->account['id'],
			'event' => $event,
			'data' => serialize($status)
		));
	}
}
