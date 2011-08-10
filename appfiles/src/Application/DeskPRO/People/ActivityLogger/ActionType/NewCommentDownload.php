<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category People
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\ActivityLogger\ActionType;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\CommentAbstract;
use Application\DeskPRO\Entity\PersonActivity;
use Application\DeskPRO\People\PersonContextInterface;

use Orb\Util\Arrays;

class NewCommentDownload extends NewCommentAbstract
{
	public function getDetails()
	{
		$details = parent::getDetails();
		$details['download_id'] = $this->comment->download['id'];
		$details['download_title'] = $this->comment->download['title'];
	}
}