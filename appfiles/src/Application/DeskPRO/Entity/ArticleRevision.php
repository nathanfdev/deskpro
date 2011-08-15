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

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;
use Application\DeskPRO\Markdown;

/**
 * Article revisions
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleRevision")
 * @ORM_Mapping\Table(name="article_revisions")
 */
class ArticleRevision extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @ORM_Mapping\id
	 * @ORM_Mapping\ManyToOne(targetEntity="Article")
	 * @ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}
}