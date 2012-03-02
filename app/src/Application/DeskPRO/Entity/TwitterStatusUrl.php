<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/


/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @author Pierre Minnieur <pm@pierre-minnieur.de>
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

/**
 * Twitter Status Url
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_statuses_urls")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterStatusUrl extends \Application\DeskPRO\Domain\DomainObject
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
	 * @ORM_Mapping\Column(name="url", type="string", length=255)
	 */
	protected $url;

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
		if ($id && $status = App::getOrm()->getRepository('DeskPRO:TwitterStatus')->find($id)) {
			$this->status = $status;
		} else {
			$this->status = null;
		}
	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	static public function createFromXML($url)
	{
		$entity = new self();
		$entity['url'] = (string) $url->url;
		$entity['starts'] = (integer) $url->attributes()->start;
		$entity['ends'] = (integer) $url->attributes()->end;

		return $entity;
	}

	/**
	 * @param \SimpleXMLElement|\Zend\Rest\Client\Result $url
	 * @return \Application\DeskPRO\Entity\TwitterStatusUrl
	 */
	static public function createFromJson(array $url)
	{
		$entity = new self();
		$entity['url'] = $url['url'];
		$entity['starts'] = $url['indices'][0];
		$entity['ends'] = $url['indices'][1];

		return $entity;
	}
}
