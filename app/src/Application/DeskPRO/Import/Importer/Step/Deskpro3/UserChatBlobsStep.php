<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step\Deskpro3;

use Orb\Util\Strings;
use Orb\Data\ContentTypes;

class UserChatBlobsStep extends AbstractBlobsStep
{
	public static function getTitle()
	{
		return 'Import User Chat Blobs';
	}

	public function getTable()
	{
		return 'chat_attachment';
	}
}
