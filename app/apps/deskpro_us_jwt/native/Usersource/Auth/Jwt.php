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

namespace deskpro_us_jwt\Usersource\Auth;

use Orb\Auth\Adapter;
use Orb\Auth\Adapter\AbstractCallbackAdatper;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Auth\StateHandler\StateHandlerInterface;
use Orb\Util\Arrays;

class Jwt extends AbstractCallbackAdatper
{
	/**
	 * @var \Orb\Log\Logger
	 */
	protected $logger;

	/**
	 * @var \Orb\Util\OptionsArray
	 */
	protected $options;

	public function __construct(array $options)
	{
		$this->initOptions();
		$this->options->setArray($options);
	}

	protected function initOptions()
	{
		$this->options = new \Orb\Util\OptionsArray(array(
			'url' => '',
			'secret' => '',
			'login_custom_text' => 'Login (JWT)'
		));
	}


	/**
	 * Process the callback and return a final result.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateCallback(array $callback_data, StateHandlerInterface $state)
	{
		try {
			$jwt     = $callback_data['jwt'];
			$secret  = $this->options->get('secret');
			$payload = \JWT::decode($jwt, $secret, true);
			$payload_array = Arrays::fromStdClass($payload);

			$identity = new Identity($payload_array['id'], $payload_array);
			$identity->setFriendlyIdentity($payload_array['email']);
			$result = new Result(Result::SUCCESS, $identity);
		} catch (\Exception $e) {
			$result = new Result(Result::FAILURE_EXCEPTION, null, array(Result::MSG_EXCEPTION => $e));
		}

		return $result;
	}


	/**
	 * Initialize the auth process by setting state, and returning a redirect result.
	 *
	 * @return \Orb\Auth\Result
	 */
	protected function authenticateInitialize(StateHandlerInterface $state)
	{
		// return a success result if we detect they are already logged in
		$result = new Result(Result::REQUIRES_REDIRECT, null, array(Result::MSG_REDIRECT => $this->options->get('url')));

		return $result;
	}
}
