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

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Oauth
{
	/**
	 * @var \Zend\OAuth\Consumer
	 */
	static protected $consumer;

	/**
	 * Retrieve Twitter Application OAuth Consumer Key.
	 *
	 * @return string
	 * @fixme read value from config file
	 */
	static public function getConsumerKey()
	{
		return '8F0tLXjdjVDDsovNjWJw';
	}

	/**
	 * Retrieve Twitter Application OAuth Consumer Secret.
	 *
	 * @return string
	 * @fixme read value from config file
	 */
	static public function getConsumerSecret()
	{
		return '2naz7yxp6TBnEXwP9EnzLC0WU2Vwg60vm17tMntOo';
	}

	/**
	 * @param string $callbackUrl (optional)
	 * @return \Zend\Oauth\Consumer
	 */
	static public function getConsumer($callbackUrl = null)
	{
		if (null !== self::$consumer) {
			return self::$consumer;
		}

		// @TODO make configurable
		$config = array(
			'siteUrl'        => 'http://twitter.com/oauth',
			'consumerKey'    => self::getConsumerKey(),
			'consumerSecret' => self::getConsumerSecret()
		);

		// apply callback url on non-cli environments
		if (null !== $callbackUrl) {
			$config['callbackUrl'] = $callbackUrl;
		}

		// create Zend Oauth Consumer
		self::$consumer = new \Zend\OAuth\Consumer($config);

		return self::$consumer;
	}
}
