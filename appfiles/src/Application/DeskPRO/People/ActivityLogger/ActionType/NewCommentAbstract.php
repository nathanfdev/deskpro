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

abstract class NewCommentAbstract extends ActionTypeAbstract
{
	protected $comment;


	/**
	 * @param \Application\DeskPRO\Entity\Person $person
	 * @param \Application\DeskPRO\Entity\CommentAbstract $comment
	 */
	public function __construct(Person $person, CommentAbstract $comment)
	{
		$this->person = $person;
		$this->comment = $comment;
	}


	/**
	 * Get a plain array of details that'll be stored in the databaes
	 * @return array
	 */
	public function getDetails()
	{
		return array(
			'comment_id' => $this->comment['id'],
			'comment'    => $this->comment['content'],
		);
	}
}