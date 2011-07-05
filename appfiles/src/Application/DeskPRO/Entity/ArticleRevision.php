<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Markdown;

/**
 * Article revisions
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleRevision")
 * @orm:Table(name="article_revisions")
 */
class ArticleRevision extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @orm:id
	 * @orm:ManyToOne(targetEntity="Article")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}