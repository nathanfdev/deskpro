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
use Application\DeskPRO\Entity\News;
use Application\DeskPRO\Entity\Person;

class NewNews
{
	public $title;
	public $category_id;
	public $status;
	public $content;

	public $slug;
	public $labels = array();
	public $attach = array();

	protected $_news;

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

		$news = new News();
		$news->person = $this->_person_context;
		$news->title = $this->title;
		$news->content = $this->content;
		$news->slug = $this->slug;
		$news->setStatusCode($this->status);

		$cat = $this->_em->find('DeskPRO:NewsCategory', $this->category_id);
		$news->category = $cat;

		$news->getLabelManager()->setLabelsArray($this->labels);

		$this->_em->persist($news);
		$this->_em->flush();
		$this->_em->commit();

		$this->_news = $news;
	}

	public function getNews()
	{
		return $this->_news;
	}
}
