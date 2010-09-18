<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="templates")
 */
class Template extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * The style ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="style_id", type="integer")
	 */
	protected $style_id = null;


	/**
	 * The style this template belongs to
	 *
	 * @var Style
	 * @OneToOne(targetEntity="Style")
	 * @JoinColumn(name="style_id", referencedColumnName="id")
	 */
	protected $style;


	/**
	 * The path of the template
	 *
	 * @var string
	 * @Column(name="path", type="string", length=255)
	 */
	protected $path;


	/**
	 * @var string
	 * @Column(name="template", type="text")
	 */
	protected $template;


	/**
	 * @var string
	 * @Column(name="template_compiled", type="text")
	 */
	protected $template_compiled;

	/**
	 * @var \DateTime
	 * @Column(name="created_at",type="datetime")
	 */
	protected $created_at;


	/**
	 * @var \DateTime
	 * @Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;

	/** @PrePersist */
	public function incCreatedAt()
	{
		$this->created_at = $this->updated_at = new \DateTime();
	}

	/** @PreUpdate */
	public function incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}