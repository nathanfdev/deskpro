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

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use Application\DeskPRO\Markdown;

use \Orb\Util\Strings;

/**
 * Ideas status types for accepted/declined statuses
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Idea")
 * @orm:Table(name="idea_statuses")
 */
class IdeaStatus extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_ACCEPTED = 'accepted';
	const STATUS_DECLINED = 'declined';

	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @orm:Column(name="status_type", type="string", length=255)
	 */
	protected $status_type;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;
}