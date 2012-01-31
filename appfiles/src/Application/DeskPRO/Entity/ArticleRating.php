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

use Orb\Util\Strings;

/**
 * Ratings on ideas
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="article_ratings")
 */
class ArticleRating extends \Application\DeskPRO\Domain\DomainObject
{
	const RATE_POSITIVE = 1;
	const RATE_NEGATICE = -1;

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\Article
	 * @ORM_Mapping\ManyToOne(targetEntity="Article", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="article_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $article = null;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 * @ORM_Mapping\ManyToOne(targetEntity="Person", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="person_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $person = null;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 * @ORM_Mapping\ManyToOne(targetEntity="Visitor", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="visitor_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $visitor = null;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="rating", type="integer")
	 */
	protected $rating = 1;

	/**
	 * Comment left by the user (usually in the case of negative)
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="comment", type="text")
	 */
	protected $comment = '';

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
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
