<?php

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
	 * @return array
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
	public function enqueueStatus($status) {
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
			'event' => $event,
			'data' => serialize($status)
		));
	}
}
