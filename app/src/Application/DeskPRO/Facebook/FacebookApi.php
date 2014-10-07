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
 * @subpackage Facebook
 */

namespace Application\DeskPRO\Facebook;


use Application\DeskPRO\Entity\FacebookApp;
use Application\DeskPRO\Entity\FacebookPage;
use Guzzle\Http\Client;

class FacebookApi
{
	/**
	 * @var Client
	 */
	protected $facebook;

	/**
	 * @var FacebookApp
	 */
	protected $app;

	/**
	 * @var string app token using for this request
	 */
	protected $app_token;

	public function __construct(FacebookApp $app)
	{
		$this->app = $app;
		//$this->facebook = new \Facebook(array('appId' => $app->app_id, 'secret' => $app->app_secret));
	}


	public function getClient()
	{
		if ($this->facebook) {
			return $this->facebook;
		}

		$this->facebook = new Client('https://graph.facebook.com');

		if (!$this->app_token) {
			$output = $this->sendGetRequest(
				'/oauth/access_token', array(
					'client_id' => $this->app->app_id,
					'client_secret' => $this->app->app_secret,
					'grant_type' => 'client_credentials'
				)
			);

			$this->app_token = $output['access_token'];
		}

		return $this->facebook;
	}


	/**
	 * The short-term token needs to be extended here for use/storage on server
	 *
	 * If the token received is not used for 60 days, it expires and user must re-setup their channel
	 *
	 * @param FacebookPage $page
	 * @return bool true if valid user token is now in $page
	 */
	public function extendUserToken(FacebookPage $page, $extend_page_token = true)
	{
		$output = $this->sendGetRequest(
			'/oauth/access_token', array(
				'grant_type'        => 'fb_exchange_token',
				'client_id'         => $this->app->app_id,
				'client_secret'     => $this->app->app_secret,
				'fb_exchange_token' => $page->user_token
			)
		);

		if ($output) {
			$page->user_token         = $output['access_token'];
			$page->date_user_token_received = new \DateTime('now');
		}

		if ($extend_page_token) {
			$this->extendPageToken($page, false);
		}

		return true;
	}

	/**
	 * The short-term page token needs to be extended here for use/storage on server
	 *
	 * @param FacebookPage $page
	 * @return bool true if valid user token is now in $page
	 */
	public function extendPageToken(FacebookPage $page, $extend_user_token = true)
	{
		if ($extend_user_token) {
			$this->extendUserToken($page, false);
		}

		$output = $this->sendGetRequest(
			'/me/accounts', array(
				'access_token' => $page->user_token
			)
		);

		if ($output) {
			foreach ($output['data'] as $page_info) {
				if ($page->graph_id == $page_info['id']) {
					$page->page_token = $page_info['access_token'];
					break;
				}
			}
		}


		return true;
	}

	public function subscribeToFeed(FacebookPage $page)
	{

		$output = $this->sendPostRequest(
			sprintf('/%s/tabs', $page->graph_id),
			array(
				'app_id' => $page->app->app_id,
				'access_token' => $page->page_token
			)
		);

		if ($output['success']) {
			$params = array(
				'object'       => 'page',
				'callback_url' => '107.170.193.140/fb.php',
				'fields'       => 'feed',
				'verify_token' => 'okokok',
				'access_token' => $this->app_token
			);
			$output = $this->sendPostRequest(
				sprintf('/%s/subscriptions', $page->app->app_id),
				$params
			);
		}

		return true;
	}


	private function sendGetRequest($uri, array $params)
	{
		// TODO: wrap in try/catch ?
		$fb = $this->getClient();
		$request = $fb->get($uri);

		foreach ($params as $key => $val) {
			$request->getQuery()->set($key, $val);
		}

		$res = $request->send();

		$output = array();
		if ('text/javascript; charset=UTF-8' == $res->getContentType()) {
			$output = $res->json();
		} else {
			$body = $res->getBody();
			parse_str($body, $output);
		}

		return $output;
	}


	private function sendPostRequest($uri, array $params)
	{
		// TODO: wrap in try/catch ?
		$fb = $this->getClient();
		$request = $fb->post($uri, array(), $params);

		$res = $request->send();

		$output = array();
		if ('text/javascript; charset=UTF-8' == $res->getContentType()) {
			$output = $res->json();
		} else {
			$body = $res->getBody();
			parse_str($body, $output);
		}

		return $output;
	}
}
 