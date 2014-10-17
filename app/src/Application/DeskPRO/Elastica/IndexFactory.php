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

namespace Application\DeskPRO\Elastica;

use Elastica\Client;

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

class IndexFactory
{
	/**
	 * @var \Elastica\Client
	 */
	private $client;


	/**
	 * @param Client $client
	 */
	public function __construct(Client $client)
	{
		$this->client = $client;
	}


	/**
	 * @param string $index_name
	 * @return \Elastica\Index
	 */
	public function getIndex($index_name)
	{
		if (defined('DPC_IS_CLOUD') && DPC_IS_CLOUD) {
			return $this->client->getIndex($index_name . '_' . DPC_SITE_ID);
		} else if (defined('DP_ELASTIC_INDEX')) {
			return $this->client->getIndex(DP_ELASTIC_INDEX);
		} else {
			return $this->client->getIndex($index_name);
		}
	}
}