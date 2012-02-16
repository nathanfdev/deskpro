<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category DependencyInjection
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\FileStorage\Database as DatabaseStorage;
use Application\DeskPRO\FileStorage\Filesystem as FilesystemStorage;

class FilestorageService
{
	public static function create(DeskproContainer $container)
	{
		switch ($container->getSetting('core.filestorage_method')) {
			case 'fs':
				$s = new FilesystemStorage($container->getBlobDir(), $container->getDb());
				return $s;
				break;

			default:
				$s = new DatabaseStorage($container->getDb());
				return $s;
				break;
		}
	}
}
