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
 * Stores which documents have been boosted, and by which terms
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="search_term_boosters")
 */
class SearchTermBoost extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * A 'voted' boost means the boost comes from a user
	 * upvoting a particular document after coming from a search.
	 */
	const METHOD_VOTE = 'vote';

	/**
	 * An 'agent' boost means an agent has manually entered a boost term
	 */
	const METHOD_AGENT = 'agent';



	/**
	 * @var string
	 * @ORM_Mapping\Column(name="object_type", type="string", length=100)
	 * @ORM_Mapping\Id
	 */
	protected $object_type;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="object_id", type="integer")
	 * @ORM_Mapping\Id
	 */
	protected $object_id = null;

	/**
	 * Is this an agent bossted term?
	 *
	 * If not, then the b
	 *
	 * @var bool
	 * @ORM_Mapping\Column(name="is_user", type="boolean")
	 */
	protected $boosted_method = false;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="boosted_terms", type="string", length=255)
	 */
	protected $boosted_terms;
}
