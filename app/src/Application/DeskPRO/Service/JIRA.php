<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\Service;


use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\JIRA\Api;
use Application\DeskPRO\JIRA\Meta;

class JIRA
{
	const PARAM_COMMENTS    = 'comments';
	const PARAM_META        = 'meta';
	const PARAM_URL         = 'url';
	const PARAM_CONSUMER    = 'consumer_key';
	const PARAM_TOKENS      = 'oauth_tokens';
	const PARAM_KEY         = 'core_jira.private_key';

	/**
	 * @var DeskproContainer
	 */
	protected $container;

	/**
	 * @var Api|null
	 */
	protected $api;

	/**
	 * @var AppInstance
	 */
	protected $app = false;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * @return mixed
	 */
	public function isEnabled()
	{
		return $this->getApp() && $this->getApp()->getSetting(self::PARAM_TOKENS);
	}

	/**
	 * @return string|null
	 */
	public function getConsumerKey()
	{
		if (!$app = $this->getApp()) {
			return null;
		}
		return $app->getSetting(self::PARAM_CONSUMER);
	}

	/**
	 * @return string|null
	 */
	public function getUrl()
	{
		if (!$app = $this->getApp()) {
			return null;
		}
		return $app->getSetting(self::PARAM_URL);
	}

	/**
	 * @return string|null
	 */
	public function getPrivateKey()
	{
		return $this->container->getSetting(self::PARAM_KEY);
	}

	/**
	 * @return Api
	 */
	public function getApi()
	{
		if (!$this->api) {
			$this->api = new Api($this);
		}

		return $this->api;
	}

	/**
	 * @return AppInstance|null
	 */
	protected function getApp()
	{
		$rep = $this->container->getEm()->getRepository('DeskPRO:AppInstance');
		if (false === $this->app) {
			$this->app = $rep->getInstanceByName('deskpro_jira2');
		}

		return $this->app;
	}

	/**
	 * @return mixed|null
	 */
	public function getTokens()
	{
		if (!$app = $this->getApp()) {
			return array();
		}

		if (!$tokens = $app->getSetting(self::PARAM_TOKENS)) {
			return array();
		}

		return $tokens;
	}

	/**
	 * @param array $tokens
	 */
	public function setTokens(array $tokens)
	{
		if ($app = $this->getApp()) {
			$app->setSetting(self::PARAM_TOKENS, $tokens);
			$this->container->getEm()->flush($app);
		}
	}

	/**
	 * @param array $properties
	 * @return Meta
	 * @throws \Exception
	 */
	public function updateMeta(array $properties = array())
	{
		if (!$app = $this->getApp()) {
			return null;
		}

		try {

			$res = $this->getApi()->get('/issue/createmeta', array('expand' => 'projects.issuetypes.fields'));
			$priority = $this->getApi()->get('/priority');
			$fields = $this->getApi()->get('/field');
			$properties['projects'] = isset($res['projects']) ? $res['projects'] : array();
			$properties['priorities'] = $priority;
			$properties['fields'] = $fields;

			$meta = Meta::fromArray($properties);

			$app->setSetting(self::PARAM_META, $meta->toArray());
			$this->container->getEm()->flush($app);

		} catch (\Exception $e) {
			// todo
			throw $e;
		}

		return $meta;
	}

	/**
	 * @return Meta|null
	 */
	public function getMeta()
	{
		if (!$app = $this->getApp()) {
			return null;
		}

		if ($metaData = $app->getSetting(self::PARAM_META)) {
			$meta = Meta::fromArray($metaData);
		} else {
			$meta = $this->updateMeta();
		}

		return $meta;
	}




	// todo move these methods to Api?

	/**
	 * @param $jql
	 * @return array
	 * @throws \Exception
	 */
	public function searchIssues($jql)
	{
		try {
			$result = $this->getApi()->post('/search', array(
				'jql' => $jql,
				'fields' => $this->getMeta()->getAllFields(),
				'expand' => array('names', 'renderedFields'),
			));
		} catch (\Exception $e) {
			// todo
			throw $e;
		}

		return $result;
	}

	public function createComment($issueId, $message)
	{
		try {

			$result = $this->getApi()->post('/issue/' . $issueId . '/comment?expand=renderedBody', array(
				'body' => $message,
			));

		} catch (\Exceptions $e) {
			// todo
			throw $e;
		}

		return $result;
	}
} 