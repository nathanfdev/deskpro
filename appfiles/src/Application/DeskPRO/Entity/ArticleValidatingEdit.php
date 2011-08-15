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

use Orb\Util\Strings;

use FineDiff;

/**
 * Tracks an article edit that needs to be validated
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\ArticleValidatingEdit")
 * @ORM_Mapping\Table(name="article_validating_edits")
 */
class ArticleValidatingEdit extends \Application\DeskPRO\Domain\DomainObject
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
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $person = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="excerpt", type="string", length=1000)
	 */
	protected $excerpt = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content;

	/**
	 * Render a diff
	 * 
	 * @return string
	 */
	public function renderDiff()
	{
		$from_string = $this->article['content'];
		$to_string   = $this->content;

		$diff = new FineDiff(
			$from_string,
			$to_string,
			FineDiff::$wordGranularity
		);

		$edits = $diff->getOps();
		$rendered_diff = $diff->renderDiffToHTML();

		$rendered_diff = nl2br($rendered_diff);

		return $rendered_diff;
	}
}