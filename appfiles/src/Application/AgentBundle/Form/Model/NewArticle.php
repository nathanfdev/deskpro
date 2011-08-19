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
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\Person;

class NewArticle
{
	public $title;
	public $category_id;
	public $status;
	public $content;

	public $slug;
	public $labels = array();
	public $attach = array();

	protected $_article;

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

		$article = new Article();
		$article->person = $this->_person_context;
		$article->setStatusCode($this->status);
		$article->title = $this->title;
		$article->content = $this->content;
		$article->slug = $this->slug;

		$cat = $this->_em->find('DeskPRO:ArticleCategory', $this->category_id);
		$article->addToCategory($cat);

		$article->getLabelManager()->setLabelsArray($this->labels);

		$this->_em->persist($article);
		$this->_em->flush();
		$this->_em->commit();

		$this->_article = $article;
	}

	public function getArticle()
	{
		return $this->_article;
	}
}