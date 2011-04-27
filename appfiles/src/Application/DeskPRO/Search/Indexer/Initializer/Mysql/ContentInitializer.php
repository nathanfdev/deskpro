<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Search
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\IndexInitializer\Mysql;

use Application\DeskPRO\App;
use Application\DeskPRO\Search\IndexInitializer\ContentInitializer as BaseContentInitializer;

class ContentInitializer extends BaseContentInitializer
{
	public function preRun()
	{
		App::getDb()->exec("DELETE FROM content_search WHERE object_type IN ('article','download','idea','news')");
		App::getDb()->exec("DELETE FROM content_search_attribute WHERE object_type IN ('article','download','idea','news')");
	}

}