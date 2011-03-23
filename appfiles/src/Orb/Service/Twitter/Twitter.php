<?php

namespace Orb\Service\Twitter;

use \Application\DeskPRO\App;

class Twitter extends \Zend_Service_Twitter
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

		return new self(array(
			'accessToken' => $accessToken
		), $consumer);
	}

    /**
     * Retweet a specified status.
     *
     * @param  string $id
     * @return Zend_Rest_Client_Result
     * @throws Zend_Http_Client_Exception if HTTP request fails or times out
     */
    public function statusRetweet($id)
    {
        $this->_init();
        $path = '/1/statuses/retweet/'.$this->_validInteger($id).'.xml';
        $response = $this->_post($path);
        return new \Zend_Rest_Client_Result($response->getBody());
    }
}
