<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\Elastica;

use Application\DeskPRO\Exception\MissingConfigurationException;
use Application\DeskPRO\Settings\Settings;
use Orb\Util\Arrays;
use Orb\Util\OptionsArray;

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

class ClientFactory
{
	/**
	 * @var \Application\DeskPRO\Settings\Settings
	 */
	private $settings;


	/**
	 * @param Settings $settings
	 */
	public function __construct(Settings $settings)
	{
		$this->settings = $settings;
	}


	/**
	 * @param array $config
	 * @return Client
	 */
	public function createSystemClientByConfig(array $config)
	{
		if (isset($config['connections'][0]['host']) && $config['connections'][0]['host'] == 'DEFAULT') {
			return $this->createClientById('default');
		} else {
			return $this->createClientByConfig($config);
		}
	}


	/**
	 * @param string $id
	 * @return Client
	 */
	public function createClientById($id)
	{
		if ($this->settings->get("elastica.clients.$id.url")) {

			$config = self::createConfigFromUrl($this->settings->get("elastica.clients.$id.url"));

		} else {
			$config = array(
				'host'      => $this->settings->get("elastica.clients.$id.host"),
				'port'      => $this->settings->get("elastica.clients.$id.port"),
				'path'      => $this->settings->get("elastica.clients.$id.path") ? : null,
				'transport' => $this->settings->get("elastica.clients.$id.transport") ? : null
			);
		}

		if (!$config['host'] || !$config['port']) {
			throw new MissingConfigurationException;
		}

		$config = Arrays::removeFalsey($config);

		return $this->createClientByConfig($config);
	}


	/**
	 * @param string $url
	 * @return array
	 * @throws \Application\DeskPRO\Exception\MissingConfigurationException
	 */
	public static function createConfigFromUrl($url)
	{
		if (!$url) {
			throw new MissingConfigurationException("No URL specified");
		}

		if (!preg_match('#^\w+://#', $url)) {
			$url = 'http://' . $url;
		}

		$url_info = parse_url($url);
		if (!$url_info) {
			throw new MissingConfigurationException("Invalid URL");
		}

		$url_info = new OptionsArray($url_info);
		if (!$url_info->has('host')) {
			throw new MissingConfigurationException("Missing host");
		}

		$config = array(
			'host'      => $url_info->host,
			'port'      => $url_info->port ?: 9200,
			'path'      => $url_info->path ?: null,
			'transport' => strtolower($url_info->get('scheme', 'http')) == 'https' ? 'Https' : 'Http'
		);

		if ($url_info->user && $url_info->pass) {
			$config['headers'] = array('Authorization'=> 'Basic '.  base64_encode($url_info->user .':'. $url_info->pass));
		}

		return $config;
	}


	/**
	 * @param array $config
	 * @return Client
	 */
	public function createClientByConfig(array $config)
	{
		$config = new OptionsArray($config);

		$client = new Client(array(
			'host'      => $config->get('host', 'localhost'),
			'port'      => $config->get('port', 9200),
			'path'      => $config->get('path', null),
			'transport' => $config->get('transport', null),
			'headers'   => $config->get('headers', array()),
			'log'       => $config->get('log', null)
		));

		if ($config->get('logger')) {
			$client->setLogger($config->get('logger'));
		}

		return $client;
	}
}