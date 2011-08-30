<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AgentBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AgentBundle\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Idea;
use Application\DeskPRO\Entity\Person;

class NewIdea
{
	public $title;
	public $category_id;
	public $status_code;
	public $content;

	public $slug;
	public $labels = array();

	protected $_idea;

	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $_em;

	public function __construct(Person $person_context)
	{
		$this->_person_context = $person_context;

		$this->_em = App::getOrm();
	}

	public function save()
	{
		$this->_em->beginTransaction();

		$idea = new Idea();
		$idea->person = $this->_person_context;
		$idea->setStatusCode($this->status_code);
		$idea->title = $this->title;
		$idea->content = $this->content;
		$idea->slug = $this->slug;

		$cat = $this->_em->find('DeskPRO:IdeaCategory', $this->category_id);
		$idea->category = $cat;

		$idea->getLabelManager()->setLabelsArray($this->labels);

		$this->_em->persist($idea);
		$this->_em->flush();
		$this->_em->commit();

		$this->_idea = $idea;
	}

	public function getIdea()
	{
		return $this->_idea;
	}
}
