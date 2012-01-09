<?php
/**
 * DeskPRO
 *
 * @package StaticLoader
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\StaticLoader;

use Application\DeskPRO\App;

/**
 * This static loader is used with the DI to create a search adapter.
 *
 * @see \Application\DeskPRO\DependencyInjection\SearchExtension
 */
class SearchAdapter
{
	public static function getSearchAdapter()
	{
		$adapter_name = strtolower(App::getConfig('search.adapter'));
		$config = App::getConfig('search.options');

		switch ($adapter_name) {
			case 'elastic':
				$adapter = \Application\DeskPRO\Search\Adapter\ElasticAdapter::create($config['host'], $config['port']);
				break;

			default:
				$adapter = new \Application\DeskPRO\Search\Adapter\MysqlAdapter();
				break;
		}

		if (!$adapter) {
			throw new \Exception('No search adapter');
		}

		return $adapter;
	}
}
