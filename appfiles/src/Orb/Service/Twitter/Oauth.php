<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Oauth
{
	/**
	 * @var \Zend_Oauth_Consumer
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
	 * @param Boolean $isConsole Whether we are in a Console environment (optional)
	 * @return \Zend_Oauth_Consumer
	 */
	static public function getConsumer($isConsole = false)
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
		if (true !== $isConsole) {
			// @TODO we may start the router/container manually in CLI?
			$config['callbackUrl'] = App::getContainer()->get('router')
				->generate('admin_twitter_accounts_authorize', array(), true);
		}

		// create Zend Oauth Consumer
		self::$consumer = new \Zend_Oauth_Consumer($config);

		return self::$consumer;
	}
}
