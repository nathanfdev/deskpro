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
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 */
	protected $id = null;


	/**
         * The access token recieved from Twitter
         *
	 * @var string
	 * @orm:Column(name="access_token", type="string", length=255)
	 */
	protected $access_token;


	/**
         * Twitter handle of the account
         *
	 * @var string
	 * @orm:Column(name="twitter_handle", type="string", length=255)
	 */
	protected $twitter_handle;

}