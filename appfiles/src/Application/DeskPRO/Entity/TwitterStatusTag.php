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
 * Twitter Status Tag
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_statuses_tags")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatusTag extends \Application\DeskPRO\Domain\DomainObject
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
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterStatus")
	 * @ORM_Mapping\JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="hash", type="string", length="255")
	 */
	protected $hash;

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
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $tag
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	static public function createFromXML($tag)
	{
		$entity = new self();
		$entity['hash'] = (string) $tag->text;
		$entity['starts'] = (integer) $tag->attributes()->start;
		$entity['ends'] = (integer) $tag->attributes()->end;

		return $entity;
	}

	/**
	 * @param array $status
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	static public function createFromJson(array $tag)
	{
		$entity = new self();
		$entity['hash'] = $tag['text'];
		$entity['starts'] = $tag['indices'][0];
		$entity['ends'] = $tag['indices'][1];

		return $entity;
	}
}
