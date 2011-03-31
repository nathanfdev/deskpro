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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter Status Tag
 *
 * @orm:Entity
 * @orm:Table(name="twitter_statuses_tags")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatusTag extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="AUTO")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterStatus
	 * @orm:ManyToOne(targetEntity="TwitterStatus")
	 * @orm:JoinColumn(name="status_id", referencedColumnName="id")
	 */
	protected $status;

	/**
	 * @var string
	 * @orm:Column(name="hash", type="string", length="255")
	 */
	protected $hash;

	/**
	 * @var integer
	 * @orm:Column(name="starts", type="integer")
	 */
	protected $starts = 0;

	/**
	 * @var integer
	 * @orm:Column(name="ends", type="integer")
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
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $tag
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	static public function createFromXML($tag)
	{
		$entity = new self();
		print_r($tag);

		return $entity;
	}

	/**
	 * @param array $status
	 * @return \Application\DeskPRO\Entity\TwitterStatusTag
	 */
	static public function createFromJson(array $tag)
	{
		$entity = new self();
		$entity['hash']   = $tag['text'];
		$entity['starts'] = $tag['indices'][0];
		$entity['ends']   = $tag['indices'][1];

		return $entity;
	}
}