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

namespace Application\DeskPRO\JobQueue;

interface JobProcessorInterface
{
	/**
	 * $job['data'] MUST be the payload that the job works with. We pass the whole dbal row in
	 * because otherwise the processor can't make its own decisions about logging and job status
	 *
	 * @param array $job the job row from the dbal
	 * @return null
	 */
	public function execute(array $job);


	/**
	 * $job['type'] will usually be checked here to determine if its the right type of job for this processor
	 *
	 * @param array $job the job row from the dbal
	 * @return bool TRUE if this processor can handle the job, FALSE otherwise
	 */
	public function canHandle(array $job);
}
