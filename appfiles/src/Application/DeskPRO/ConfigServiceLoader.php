<?php
/**
 * DeskPRO
 *
 * @package StaticLoader
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO;

use Application\DeskPRO\App;
use Application\DeskPRO\Elastica\ElasticaManager;

/**
 * A loader for various things from config
 */
class ConfigServiceLoader
{
	function loadElasticaManager()
	{
		$default_client = App::getConfig('elastica.default_client');
		$clients = App::getConfig('elastica.clients');

		if (!$clients) {
			return null;
		}

		if (!isset($clients[$default_client])) {
			throw new \RuntimeException("Invalid default elastica client set. No client of name `$default_client` was configured.");
		}

		$manager = new ElasticaManager();

		foreach ($clients as $name => $info) {
			$manager->createClientObject($name, $info['host'], $info['port']);
		}

		$manager->setDefaultClientName($default_client);

		// Add indexes
		$manager->createIndexObject('content');

		return $manager;
	}
}