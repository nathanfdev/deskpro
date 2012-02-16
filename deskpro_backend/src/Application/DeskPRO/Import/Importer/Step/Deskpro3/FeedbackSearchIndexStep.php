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

use Application\DeskPRO\Entity\ArticleCategory;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;

class FeedbackSearchIndexStep extends AbstractSearchIndexStep
{
	public static function getTitle()
	{
		return 'Initialize Feedback Search';
	}

	public function getTable() { return 'feedback'; }
	public function getEntity() { return 'DeskPRO:Feedback'; }
	public function getContentType() { return 'feedback'; }
}
