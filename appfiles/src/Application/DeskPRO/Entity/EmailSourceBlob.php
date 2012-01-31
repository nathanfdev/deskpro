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
 * Raw email data
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="email_sources_blobs")
 */
class EmailSourceBlob extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY") @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var \Application\DeskPRO\Entity\EmailSource
	 * @ORM_Mapping\ManyToOne(targetEntity="EmailSource")
	 * @ORM_Mapping\JoinColumn(name="source_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $source = null;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="data", type="dpblob")
	 */
	protected $data;
}
