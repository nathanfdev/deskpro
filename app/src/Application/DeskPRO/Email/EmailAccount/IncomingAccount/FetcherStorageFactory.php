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
 * @category Entities
 */

namespace Application\DeskPRO\Email\EmailAccount\IncomingAccount;

use Application\DeskPRO\Email\EmailAccount\AccountConfigInterface;
use Application\DeskPRO\EmailGateway\FetcherStorage\FetcherStorageInterface;
use Application\DeskPRO\EmailGateway\FetcherStorage\Pop3Storage;

//TODO this is not actually used in the Runner
class FetcherStorageFactory
{
	/**
	 * @param AccountConfigInterface $config
	 * @return FetcherStorageInterface
	 * @throws \InvalidArgumentException
	 */
	public function createFetcherStorage(AccountConfigInterface $config)
	{
		switch ($config->getType()) {
			case 'pop3':  return $this->createPop3Fetcher($config);
			case 'gmail': return $this->createGmailFetcherStorage($config);
			default:
				throw new \InvalidArgumentException("Unknown incoming account type: {$config->getType()}");
		}
	}

	/**
	 * @param Pop3Config $config
	 * @return Pop3Storage
	 */
	public function createPop3FetcherStorage(Pop3Config $config)
	{
		return new Pop3Storage(
			$config->host,
			$config->port,
			$config->user,
			$config->password,
			$config->secure_mode
		);
	}


	/**
	 * @param GmailConfig $config
	 * @return Pop3Storage
	 */
	public function createGmailFetcherStorage(GmailConfig $config)
	{
		return new Pop3Storage(
			'pop.gmail.com',
			995,
			$config->user,
			$config->password,
			'ssl'
		);
	}
}