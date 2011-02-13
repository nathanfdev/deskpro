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
 * Twitter Account Search
 *
 * @orm:Table(name="twitter_accounts_searches")
 * @orm:HasLifecycleCallbacks
 */
class TwitterAccountSearch extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var \Application\DeskPRO\Entity\TwitterAccount
	 * @orm:ManyToOne(targetEntity="TwitterAccount")
	 * @orm:JoinColumn(name="account_id", referencedColumnName="id")
	 */
	protected $account;

	/**
	 * @var string
	 * @orm:Column(name="term", type="string", length="255")
	 */
	protected $term;

	/**
	 * @return TwitterAccount
	 */
	public function getAccount()
	{
		return $this->account;
	}

	/**
	 * @param TwitterAccount $account
	 */
	public function setAccount(TwitterAccount $account)
	{
		$this->account = $account;
	}

	/**
	 * @return integer
	 */
	public function getAccountId()
	{
		return null !== $this->account ? $this->account->getId() : null;
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

	/**
	 * @return string
	 */
	public function getTerm()
	{
		return $this->term;
	}

	/**
	 * @param string $term
	 */
	public function setTerm($term)
	{
		$this->term = $term;
	}
}
