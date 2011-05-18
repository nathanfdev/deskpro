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

use \Orb\Util\Strings;

/**
 * Ratings on ideas
 *
 * @orm:Entity
 * @orm:Table(name="article_ratings")
 */
class ArticleRating extends \Application\DeskPRO\Domain\DomainObject
{
	const RATE_POSITIVE = 1;
	const RATE_NEGATICE = -1;
	
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @orm:ManyToOne(targetEntity="Article", fetch="EAGER")
	 * @orm:JoinColumn(name="article_id", referencedColumnName="id")
	 */
	protected $article = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @orm:ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @orm:JoinColumn(name="person_id", referencedColumnName="id")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @orm:ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @orm:JoinColumn(name="visitor_id", referencedColumnName="id")
	 */
	protected $visitor = null;

	/**
	 * @var int
	 * @orm:Column(name="rating", type="integer")
	 */
	protected $rating = 1;

	/**
	 * Comment left by the user (usually in the case of negative)
	 * 
	 * @var string
	 * @orm:Column(name="comment", type="string", length=2500)
	 */
	protected $comment = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public function __consturct()
	{
		$this->date_created = new \DateTime();
	}

	public function setRating($rating)
	{
		$o = $this->rating;
		if ($rating >= 1) {
			$this->rating = self::RATE_POSITIVE;
		} else {
			$this->rating = self::RATE_NEGATICE;
		}

		$this->_onPropertyChanged('rating', $o, $this->rating);
	}
}