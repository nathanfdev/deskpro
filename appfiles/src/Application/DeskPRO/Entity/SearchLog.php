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

use Orb\Util\Util;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Log of searches on userend
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\SearchLog")
 * @ORM_Mapping\Table(name="searchlog")
 */
class SearchLog extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

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
	 * @var string
	 * @ORM_Mapping\Column(name="ip_address", type="string", length=30)
	 */
	protected $ip_address = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="email", type="string", length=255, nullable=true)
	 */
	protected $email = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="name", type="string", length=255, nullable=true)
	 */
	protected $name = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="query", type="string", length=1000)
	 */
	protected $query;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="num_results", type="integer")
	 */
	protected $num_results;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="date_created",type="datetime")
	 */
	protected $date_created;

	public static function create($query, $num_results, $use_request = true)
	{
		$searchlog = new self();
		$searchlog->query = $query;
		$searchlog->num_results = $num_results;

		if ($use_request && App::has('request')) {
			if (!App::getCurrentPerson()->isGuest()) {
				$searchlog->person = App::getCurrentPerson();
			}

			$searchlog->visitor = App::getSession()->getVisitor();
		}

		return $searchlog;
	}

	public function __construct()
	{
		$this->date_created = new \DateTime();
	}

	/**
	 * Sets the query after trying to normalize it a bit
	 *
	 * @param $query
	 */
	public function setQuery($query)
	{
		$query = trim($query);
		$query = preg_replace('# {2,}#', ' ', $query);
		$query = Strings::utf8_strtolower($query);
		$query = Strings:: utf8_accents_to_ascii($query);

		$this->query = $query;
	}
}
