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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

/**
 * Twitter Account Search
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="twitter_accounts_searches")
 * @ORM_Mapping\HasLifecycleCallbacks
 */
class TwitterAccountSearch extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\GeneratedValue(strategy="AUTO")
	 * @ORM_Mapping\Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 * @ORM_Mapping\ManyToOne(targetEntity="TwitterAccount", inversedBy="searches")
	 * @ORM_Mapping\JoinColumn(name="account_id", referencedColumnName="id")
	 */
	protected $account;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="term", type="string", length="255")
	 */
	protected $term;

	/**
	 * @return integer
	 */
	public function getAccountId()
	{
		if (null !== $this->account) {
			return $this->account->getId();
		}
		
		return 0;
	}

	/**
	 * @param integer $id
	 */
	public function setAccountId($id)
	{
		if ($id && $account = App::getOrm()->getRepository('DeskPRO:TwitterAccount')->find($id)) {
			$this->account = $account;
		} else {
			$this->account = null;
		}
	}
}
