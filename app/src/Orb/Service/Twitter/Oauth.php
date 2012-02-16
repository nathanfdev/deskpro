<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Oauth
{
	/**
	 * @var \Zend\Oauth\Consumer
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
		self::$consumer = new \Zend\Oauth\Consumer($config);

		return self::$consumer;
	}
}
