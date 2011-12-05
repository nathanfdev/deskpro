<?php

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Twitter Status Mention
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_statuses_mentions")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatusMention extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\GeneratedValue(strategy="AUTO")
	 * @ORM_Mapping\Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterStatus", inversedBy="mentions")
	 * @ORM_Mapping\JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterUser", inversedBy="mentions")
	 * @ORM_Mapping\JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var integer
	 * @ORM_Mapping\Column(name="starts", type="integer")
	 */
	protected $starts = 0;

	/**
	 * @var integer
	 * @ORM_Mapping\Column(name="ends", type="integer")
	 */
	protected $ends = 0;

	/**
	 * @return integer
	 */
	public function getStatusId()
	{
		if (null !== $this->status) {
			return $this->status->getId();
		}

		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setStatusId($id)
	{
		$this->status = null;

		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->status = $status;
		}
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		return null !== $this->user ? $this->user->getId() : null;
	}

	/**
	 * @param integer $id
	 */
	public function setUserId($id)
	{
		if ($id && $user = App::getOrm()->getRepository('DeskPRO:TwitterUser')->find($id)) {
			$this->user = $user;
		} else {
			$this->user = null;
		}
	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $mention
	 * @return \Application\DeskPRO\Entity\TwitterStatusMention
	 */
	static public function createFromXML($mention)
	{
		$entity = new self();
		$entity['starts'] = (integer) $mention->attributes()->start;
		$entity['ends'] = (integer) $mention->attributes()->end;

		return $entity;
	}

	/**
	 * @param array $mention
	 * @return \Application\DeskPRO\Entity\TwitterStatusMention
	 */
	static public function createFromJson(array $mention)
	{
		$entity = new self();
		$entity['starts'] = $mention['indices'][0];
		$entity['ends'] = $mention['indices'][1];

		return $entity;
	}
}
