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



namespace Application\DeskPRO\Service;


use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\EntityRepository\RateLimitLog;
use Application\DeskPRO\ORM\EntityManager;
use Application\DeskPRO\People\PersonGuest;
use Application\DeskPRO\Settings\Settings;

class RateLimit
{
	const KEY = 'rate_limit';

	const ACT_LOGIN = 'login';
	const ACT_REGISTRATION = 'registration';
	const ACT_RESET_PWD = 'reset_password';

	const ACT_SUBMIT_COMMENT = 'submit_comment';
	const ACT_SUBMIT_FEEDBACK = 'submit_feedback';
	const ACT_SUBMIT_TICKET = 'submit_ticket';


	/** @var Settings  */
	protected $settings;

	/** @var RateLimitLog  */
	protected $rep;

	protected $params_cache = array();

	public function __construct(Settings $settings, EntityManager $em)
	{
		$this->settings = $settings;
		$this->rep = $em->getRepository('DeskPRO:RateLimitLog');
	}

	/**
	 * save action
	 * @param $action
	 * @param Person $person
	 * @param null $ip
	 * @throws \Exception
	 */
	public function saveAction($action, Person $person, $ip = null)
	{
		if (!$params = $this->getParams($action, $person, $ip)) {
			throw new \Exception('Invalid rate limit action');
		}

		$this->rep->save($action, $person, $ip);
	}

	/**
	 * response. bool for now
	 * @param $action
	 * @param Person $person
	 * @param null $ip
	 * @return bool
	 * @throws \Exception
	 */
	public function getResponse($action, Person $person, $ip = null)
	{
		if (!$params = $this->getParams($action, $person, $ip)) {
			throw new \Exception('Invalid rate limit action');
		}

		$res = $this->rep->count($action, $params['time'], $person, $ip);

		// all rate limit actions have a captcha as response, so we return bool for now
		return $res >= (int)$params['limit']
			? (bool) $params['response']
			: false;
	}

	/**
	 * params for current dataset
	 * @param $action
	 * @param Person $person
	 * @param null $ip
	 * @return array
	 */
	protected function getParams($action, Person $person, $ip = null)
	{
		$_k = sha1($action . '|' . $person['id'] . '|' . $ip);
		if (isset($this->params_cache[$_k])) {
			return $this->params_cache[$_k];
		}

		$res = array();
		foreach (array('limit', 'time', 'response') as $key) {

			// try guest first
			if ($person instanceof PersonGuest) {
				if (null !== $value = $this->settings->get(self::KEY . '.' . $action . '.guest.' . $key)) {
					$res[$key] = $value;
					continue;
				}
			}

			if (null === $value = $this->settings->get(self::KEY . '.' . $action . '.' . $key)) {
				return array();
			}
			$res[$key] = $value;
		}

		return $this->params_cache[$_k] = $res;
	}
}