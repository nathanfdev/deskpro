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
use Application\DeskPRO\Entity\Feedback;
use Application\DeskPRO\Entity\Person;

class NewFeedback
{
	public $title;
	public $category_id;
	public $status_code;
	public $content;

	public $slug;
	public $labels = array();

	protected $_feedback;

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

		$feedback = new Feedback();
		$feedback->person = $this->_person_context;
		$feedback->setStatusCode($this->status_code);
		$feedback->title = $this->title;
		$feedback->content = $this->content;
		$feedback->slug = $this->slug;

		$cat = $this->_em->find('DeskPRO:FeedbackCategory', $this->category_id);
		$feedback->category = $cat;

		$feedback->getLabelManager()->setLabelsArray($this->labels);

		$this->_em->persist($feedback);
		$this->_em->flush();
		$this->_em->commit();

		$this->_feedback = $feedback;
	}

	public function getFeedback()
	{
		return $this->_feedback;
	}
}
