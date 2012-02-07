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

/**
 * Ideas status types for accepted/declined statuses
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\FeedbackStatusCategory")
 * @ORM_Mapping\Table(name="feedback_status_categories")
 */
class FeedbackStatusCategory extends \Application\DeskPRO\Domain\DomainObject
{
	const STATUS_ACTIVE = 'active';
	const STATUS_CLOSED = 'closed';

	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 */
	protected $id = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="status_type", type="string", length=255)
	 */
	protected $status_type;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * @var int
	 * @ORM_Mapping\Column(name="display_order", type="integer")
	 */
	protected $display_order = 0;

	public function getStatusCode()
	{
		return $this->status_type . '.' . $this->id;
	}
}
