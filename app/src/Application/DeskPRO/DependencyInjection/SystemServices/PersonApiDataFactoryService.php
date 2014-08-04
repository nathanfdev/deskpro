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
 * @subpackage DependencyInjection
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;


use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Settings\Settings;

/**
 * This service should be used as a factory to create API array data from people in various contexts.
 */
class PersonApiDataFactoryService
{
	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}

	public static function create(DeskproContainer $container, array $options = null)
	{
		$settings = $container->get('deskpro.core.settings');
		$o = new static($settings);

		return $o;
	}

	/**
	 * Use this instead of $agent->toApiDAta(), whenever possible. It allows using the container dependency injection
	 * rather than using App::get('settings'), etc, directly inside of the entity itself, without repeating this logic
	 * in many controllers.
	 *
	 * @param Person $agent Expects an agent, produces common API data sent for a person
	 *
	 * @return array the API data
	 */
	public function agentToApiData(Person $agent)
	{
		$data = $agent->toApiData();

		if (!isset($data['primary_phone_number_region'])) {
			$data['primary_phone_number_region'] = $this->settings->get('core.default_country_code');
		}

		return $data;
	}
}
