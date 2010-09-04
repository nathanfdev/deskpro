<?php
/**
 * DeskPRO
 *
 * @package StaticLoader
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\StaticLoader;


/**
 * This static loader is used with the DI to get a database connection using run-time values
 * defined in the user config.php file.
 *
 * In sys/config/config.yml, the dbal connection must look something like this:
 * <code>
 * doctrine.dbal:
 *   dp_from_user_config: db
 * </code>
 *
 * Where 'db' is the key of the config we'll fetch for connection params.
 *
 * @see \Application\CoreBundle\DependencyInjection\DoctrineExtension
 */
class DatabaseConnection
{
	static function getConnection()
	{
		$args = func_get_args();

		$key = isset($args[0]['dp_from_user_config']) ? isset($args[0]['dp_from_user_config']) : 'db';

		$args[0] = array_merge($args[0], \DeskPRO\Kernel\Kernel::getUserConfig($key));

		return call_user_func_array(array('Doctrine\DBAL\DriverManager', 'getConnection'), $args);
	}
}