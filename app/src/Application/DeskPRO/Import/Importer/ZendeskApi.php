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
 * @subpackage Import
 */

namespace Application\DeskPRO\Import\Importer;

use Orb\Log\Logger;
use Orb\Service\Zendesk\Zendesk;

class ZendeskApi extends Zendesk
{
	/**
	 * How many times to try an API call before re-throwing an error?
	 * @var int
	 */
	protected $try_count = 3;

	/**
	 * The number of seconds between try attempts
	 * when the attempts are errors;
	 *
	 * @var int
	 */
	protected $try_time_error  = 2;

	/**
	 * The number of seconds between try attempts
	 * when the attempts are rate limit errors.
	 *
	 * @var int
	 */
	protected $try_time_ratelimit  = 11;

	public function sendRequest($id, $action, array $call_data = null, array $query_data = null)
	{
		$try = $this->try_count;
		while ($try-- > 0) {
			$ex  = null;
			$err = null;
			$res = null;

			try {
				$res = parent::sendRequest($id, $action, $call_data, $query_data);
			} catch (\Exception $e) {
				$ex = $e;
				$err = 'exception';
			}

			if (!$err && $res && $res->isError()) {
				$err = 'exception';
				if ($res->getHttpStatusCode() == '429') {
					$err = 'rate';
				}
			}

			// Success, return
			if (!$err) {
				return $res;
			} else {
				// No more tries, rethrow any errors
				// or return the error result from ZD
				if (!$try) {
					if ($ex) {
						throw $ex;
					} else {
						return $res;
					}

				// Try again after a sleep
				} else {
					if ($ex == 'exception') {
						sleep($this->try_time_error);
					} else {
						sleep($this->try_time_ratelimit);
					}
				}
			}
		}

		return null;
	}
}