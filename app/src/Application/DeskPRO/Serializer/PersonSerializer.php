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
 * @subpackage Serializer
 */

namespace Application\DeskPRO\Serializer;

use Application\DeskPRO\Settings\Settings;
use Orb\Serializer\SerializerInterface;
use Orb\Util\Strings;

/**
 * This service should be used as a factory to create API array data from people in various contexts.
 */
class PersonSerializer implements SerializerInterface
{
	/**
	 * @var Settings
	 */
	private $settings;

	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}

	/**
	 * @param mixed  $data   anything that the serializer can handle
	 * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
	 * @param string $format requested return format - defaults to an array
	 * @return mixed
	 */
	public function serialize($data, $view = 'default', $format = 'array')
	{
		/** @var \Application\DeskPRO\Entity\Person $data */
		$agent = $data;

		$data = $agent->toApiData();
		if (!isset($data['primary_phone_number_region']) || !$data['primary_phone_number_region']) {
			$data['primary_phone_number_region'] = $this->settings->get('core.default_country_code');
		}

		return $data;
	}


	/**
	 * @param mixed  $data   anything that the serializer can handle
	 * @param string $view   defaults to "default" but can be anything and the handlers understand what to do
	 * @param string $format requested return format - defaults to an array
	 * @return mixed
	 */
	public function supports($data, $view = 'default', $format = 'array')
	{
		// use endsWith, because doctrine entities can be proxy names
		return is_object($data) && Strings::endsWith('Application\DeskPRO\Entity\Person', get_class($data));
	}
}
