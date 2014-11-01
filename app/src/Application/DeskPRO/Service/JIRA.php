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
use Application\DeskPRO\JIRA\Api;
use Application\DeskPRO\JIRA\Meta;

class JIRA
{
	const PARAM_ENABLED = 'jira.enabled';
	const PARAM_COMMENTS = 'jira.comments_enabled';
	const PARAM_META = 'jira.meta';

	/**
	 * @var DeskproContainer
	 */
	protected $container;

	/**
	 * @var Api|null
	 */
	protected $api;

	public function __construct(DeskproContainer $container)
	{
		$this->container = $container;
	}

	/**
	 * @return mixed
	 */
	public function isEnabled()
	{
		return $this->settings->get(self::PARAM_ENABLED);
	}

	/**
	 * @return Api
	 */
	public function getApi()
	{
		if (!$this->api) {
			$this->api = new Api($this->container->getSettingsHandler());
		}

		return $this->api;
	}

	/**
	 * @param array $properties
	 * @return Meta
	 * @throws \Exception
	 */
	public function updateMeta(array $properties = array())
	{
		$meta = new Meta();

		try {
			foreach ($properties as $k => $v) {
				$meta->setDefault($k, $v);
			}
		} catch (\Exception $e) {
			// silence is a gold
		}

		$this->container->getSettingsHandler()->setSetting(self::PARAM_META, serialize($meta));
		return $meta;
	}

	/**
	 * @return Meta
	 */
	public function getMeta()
	{
		$serialized = $this->container->getSetting(self::PARAM_META);
		return $serialized ? unserialize($serialized) : $this->updateMeta();
	}
} 