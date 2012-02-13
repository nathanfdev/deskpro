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

use Orb\Util\Arrays;

/**
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\TextSnippetCategory")
 * @ORM_Mapping\Table(name="text_snippet_categories")
 */
class TextSnippetCategory extends \Application\DeskPRO\Domain\DomainObject
{
	const TPYE_TICKET  = 'ticket';
	const TPYE_CHAT    = 'chat';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * The type of snippets this cat contains
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="typename", type="string", length=30)
	 */
	protected $typename;

	/**
	 * Who created the cat
	 *
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * Teams who can use this snippet
	 *
	 * @var Doctrine\Common\Collections\ArrayCollection
	 * @ORM_Mapping\ManyToMany(targetEntity="AgentTeam", cascade={"persist", "remove", "merge"})
     * @ORM_Mapping\JoinTable(name="text_snippetcat_to_team", joinColumns={@ORM_Mapping\JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")}, inverseJoinColumns={@ORM_Mapping\JoinColumn(name="team_id", referencedColumnName="id", onDelete="cascade")})
	 */
	protected $agent_teams = null;

	/**
	 * Everyone can see it?
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_global", type="boolean")
	 */
	protected $is_global = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	public function __construct()
	{
		$this->agent_teams = new \Doctrine\Common\Collections\ArrayCollection();
	}
}
