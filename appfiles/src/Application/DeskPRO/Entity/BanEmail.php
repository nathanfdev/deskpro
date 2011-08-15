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

use \Application\DeskPRO\App;
use \Orb\Util\Strings;
use \Orb\Util\Numbers;

/**
 * Ban an email address
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\BanEmail")
 * @ORM_Mapping\Table(name="ban_emails")
 */
class BanEmail extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The banned email address
	 *
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="banned_email", type="string", length=255)
	 */
	protected $banned_email;
}