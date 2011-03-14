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
	 * @return \Zend_Oauth_Consumer
	 */
	static public function getConsumer()
	{
		if (null !== self::$consumer) {
			return self::$consumer;
		}

		// get routing service
		$router = App::getContainer()->get('router');

		// @TODO make configurable
		$config = array(
			'callbackUrl'    => $router->generate('admin_twitter_accounts_authorize', array(), true),
			'siteUrl'        => 'http://twitter.com/oauth',
			'consumerKey'    => self::getConsumerKey(),
			'consumerSecret' => self::getConsumerSecret()
		);

		// create Zend Oauth Consumer
		self::$consumer = new \Zend_Oauth_Consumer($config);

		return self::$consumer;
	}
}
