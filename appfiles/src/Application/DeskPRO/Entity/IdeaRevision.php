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

/**
 * Idea revisions
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\IdeaRevision")
 * @ORM_Mapping\Table(name="idea_revisions")
 */
class IdeaRevision extends RevisionAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="Idea")
	 * @ORM_Mapping\JoinColumn(name="idea_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $idea;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string")
	 */
	protected $title = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="content", type="text")
	 */
	protected $content = '';
}
