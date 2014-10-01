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
 * Orb
 *
 * @package Orb
 * @category Auth
 */

namespace Orb\Auth\Adapter;

use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;

class GooglePlus extends AbstractCallbackAdatper implements ExtraDetailsInterface
{
	/**
	 * @var string
	 */
	protected $cid;

	/**
	 * @var string
	 */
	protected $cs;


	public function __construct($cid, $cs)
	{
		$this->cid = $cid;
		$this->cs = $cs;
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		$client = $this->createClient();

		$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $client->createAuthUrl()));
		return $result;
	}



	/**
	 * Process the callback and return a final result.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		$client = $this->createClient();

		if (isset($_GET['code'])) {
			$client->authenticate($_GET['code']);

			if ($access_token = $client->getAccessToken()) {
				$attrs = $client->verifyIdToken()->getAttributes();

				$identity = new Identity(
					$attrs['payload']['id'],
					array(
						'email'          => $attrs['payload']['email'],
						'email_verified' => $attrs['payload']['email_verified'],
						'id'             => $attrs['payload']['id']
					)
				);
				$identity->setFriendlyIdentity($attrs['payload']['email']);

				return new Result(Result::SUCCESS, $identity);
			} else {
				return new Result(
					Result::FAILURE, null,
					array('error_code' => 'invalid_argument', 'error_message' => 'no code provided')
				);
			}
		}

		return new Result(
			Result::FAILURE, null, array('error_code' => 'invalid_argument', 'error_message' => 'no code provided')
		);
	}


	/**
	 * @return \Google_Client
	 */
	protected function createClient()
	{
		$client = new \Google_Client();
		$client->setClientId($this->cid);
		$client->setClientSecret($this->cs);
		$client->setRedirectUri($this->getCallbackUrl());
		$client->setScopes('email');

		return $client;
	}


	/**
	 * @return array
	 */
	public function getExtraDetails()
	{
		return array(
			'callback_url' => $this->getCallbackUrl()
		);
	}
}
