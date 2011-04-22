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

/**
 * Stores which documents have been boosted, and by which terms
 *
 * @orm:Entity
 * @orm:Table(name="search_term_boosters")
 * @Orm:HasLifecycleCallbacks
 */
class SearchTermBoosters extends \Application\DeskPRO\Domain\DomainObject
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
	 * @orm:Column(name="object_type", type="string", length=100)
	 * @orm:Id
	 */
	protected $object_type;

	/**
	 * @var int
	 * @orm:Column(name="object_id", type="integer")
	 * @orm:Id
	 */
	protected $object_id = null;

	/**
	 * Is this an agent bossted term?
	 *
	 * If not, then the b
	 *
	 * @var bool
	 * @orm:Column(name="is_user", type="boolean")
	 */
	protected $boosted_method = false;

	/**
	 * @var string
	 * @orm:Column(name="boosted_terms", type="string", length=255)
	 */
	protected $boosted_terms;
}