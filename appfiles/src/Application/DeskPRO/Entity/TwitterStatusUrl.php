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
 * Twitter Status Url
 *
 * @orm:Entity
 * @orm:Table(name="twitter_statuses_urls")
 * @orm:HasLifecycleCallbacks
 */
class TwitterStatusUrl extends \Application\DeskPRO\Domain\DomainObject
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
	 * @orm:Column(name="url", type="string", length="255")
	 */
	protected $url;

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
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->status = $status;
		} else {
			$this->status = null;
		}
	}

	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	static public function createFromXML($url)
	{
		$entity = new self();
		print_r($url);

		return $entity;
	}

	/**
	 * @param \SimpleXMLElement|\Zend_Rest_Client_Result $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	static public function createFromJson(array $url)
	{
		$entity = new self();
		$entity['url']    = $url['url'];
		$entity['starts'] = $url['indices'][0];
		$entity['ends']   = $url['indices'][1];

		return $entity;
	}
}