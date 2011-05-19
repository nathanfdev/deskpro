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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;
use \Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Tracks an article edit that needs to be validated
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleValidatingEdit")
 * @orm:Table(name="article_validating_edits")
 */
class ArticleValidatingEdit extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @orm:id
	 * @orm:ManyToOne(targetEntity="Article", inversedBy="comment")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id")
	 */
	protected $article;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @orm:Column(name="excerpt", type="string", length=1000)
	 */
	protected $excerpt = '';

	/**
	 * @var string
	 * @orm:Column(name="content", type="text")
	 */
	protected $content;
}