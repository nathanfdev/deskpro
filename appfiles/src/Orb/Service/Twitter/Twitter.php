<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Twitter
{
	/**
	 * @param \Zend_Oauth_Token_Access $accessToken
	 * @param \Zend_Oauth_Consuner $consumer (optional)
	 * @return \Zend_Service_Twitter
	 */
	static public function getTwitterService(\Zend_Oauth_Token_Access $accessToken, \Zend_Oauth_Consumer $consumer = null)
	{
		if (null === $consumer) {
			$consumer = Oauth::getConsumer();
		}

		return new \Zend_Service_Twitter(array(
			'accessToken' => $accessToken
		), $consumer);
	}
}
