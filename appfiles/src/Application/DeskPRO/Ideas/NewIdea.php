<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Ideas;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Visitor;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\IdeaVote;

/**
 * New idea acts as the processor and domain object for a newidea form
 */
class NewIdea
{
	public $category_id = 0;
	public $title = '';
	public $content = '';
	public $votes = 1;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 */
	protected $visitor;

	public function __construct(Person $person = null, Visitor $visitor = null)
	{
		if ($person AND $person['id']) {
			$this->person = $person;
		}

		$this->visitor = $visitor;
	}

	public function save()
	{
		App::getOrm()->beginTransaction();

		$idea = new Idea();

		if ($this->person) {
			$idea->person = $this->person;
		} else {
			//$idea->visitor = $this->visitor;
		}

		$idea['title']        = $this->title;
		$idea['content']      = $this->content;
		$idea['category_id']  = $this->category_id;
		$idea['status']       = Idea::STATUS_NEW;
		$idea['date_created'] = new \DateTime();

		App::getOrm()->persist($idea);
		App::getOrm()->flush();

		$vote = new IdeaVote();
		$vote['idea'] = $idea;
		if ($this->person['id']) {
			$vote->person = $this->person;
		} else {
			$vote->visitor = $this->visitor;
		}
		$vote['num_ratings'] = $this->votes;
		$vote['date_created'] = new \DateTime();

		App::getOrm()->persist($vote);
		App::getOrm()->flush();

		App::getOrm()->commit();

		return $idea;
	}
}
