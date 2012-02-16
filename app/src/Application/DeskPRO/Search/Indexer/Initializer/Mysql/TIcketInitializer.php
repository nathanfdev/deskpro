<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Elastica
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Search\IndexInitializer\Mysql;

use Orb\Log\Logger;
use Application\DeskPRO\App;
use Application\DeskPRO\Search\IndexInitializer\TicketInitializer as BaseTicketInitializer;

class TicketInitializer extends BaseTicketInitializer
{
	public function preRun()
	{
		App::getDb()->exec("DELETE FROM content_search WHERE object_type IN ('ticket')");
		App::getDb()->exec("DELETE FROM content_search_attribute WHERE object_type IN ('ticket')");
	}
}