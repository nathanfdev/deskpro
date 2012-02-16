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
use Application\DeskPRO\CustomFields\FieldManager;

class ArticleFieldsManagerService
{
	public static function create(DeskproContainer $container)
	{
		$m = new FieldManager(
			$container->get('doctrine.orm.entity_manager'),
			array(
				'entity_class'       => 'Application\\DeskPRO\\Entity\\CustomDefArticle',
				'entity_name'        => 'DeskPRO:CustomDefArticle',
				'data_entity_class'  => 'Application\\DeskPRO\\Entity\\CustomDataArticle',
				'data_entity_name'   => 'DeskPRO:CustomDataArticle',
			)
		);

		return $m;
	}
}
