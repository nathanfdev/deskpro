<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Basil Thoppil <basil.thoppil@deskpro.com>
 */

namespace Application\DeskPRO\Entity;

use \Application\DeskPRO\App;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\Entity;

/**
 * A Twitter Account contains twitter username and accesstoken
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\TwitterAccount")
 * @orm:Table(name="twitter_accounts")
 */
class TwitterAccount extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var integer
	 * @orm:Id
	 * @orm:GeneratedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="bigint")
	 */
	protected $id;

	/**
	 * @var string
	 * @orm:Column(name="oauth_token", type="string", length=4000)
	 */
	protected $oauth_token;

	/**
	 * @var string
	 * @orm:Column(name="oauth_token_secret", type="string", length=4000)
	 */
	protected $oauth_token_secret;

	/**
	 * @var \Application\DeskPRO\Entity\TwitterUser
	 * @orm:ManyToOne(targetEntity="TwitterUser", inversedBy="account")
	 * @orm:JoinColumn(name="user_id", referencedColumnName="id")
	 */
	protected $user;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFollowing", mappedBy="account")
	 */
	protected $following;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountFollower", mappedBy="account")
	 */
	protected $followers;

	/**
	 * @var \Doctrine\Common\Collections\ArrayCollection
	 * @orm:OneToMany(targetEntity="TwitterAccountSearch", mappedBy="account")
	 */
	protected $searches;

    /**
	 * @var \Doctrine\Common\Collections\ArrayCollection
     * @orm:ManyToMany(targetEntity="Person", inversedBy="twitter_accounts")
     * @orm:JoinTable(name="twitter_accounts_person",
     *   joinColumns={@orm:JoinColumn(name="account_id", referencedColumnName="id")},
     *   inverseJoinColumns={@orm:JoinColumn(name="person_id", referencedColumnName="id")}
     * )
     */
	protected $persons;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->followers = new \Doctrine\Common\Collections\ArrayCollection();
		$this->following = new \Doctrine\Common\Collections\ArrayCollection();
		$this->searches = new \Doctrine\Common\Collections\ArrayCollection();
		$this->persons = new \Doctrine\Common\Collections\ArrayCollection();
	}

	/**
	 * @return integer
	 */
	public function getUserId()
	{
		if (null !== $this->user) {
			return $this->user->getId();
		}

		return 0;
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
}