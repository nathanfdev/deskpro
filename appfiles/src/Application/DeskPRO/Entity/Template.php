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
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Template")
 * @orm:HasLifecycleCallbacks
 * @orm:Table(name="templates")
 */
class Template extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY")
	 * @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;

	/**
	 * The style this template belongs to
	 *
	 * @var Style
	 * @orm:ManyToOne(targetEntity="Style")
	 * @orm:JoinColumn(name="style_id", referencedColumnName="id")
	 */
	protected $style;

	/**
	 * The path of the template
	 *
	 * @var string
	 * @orm:Column(name="path", type="string", length=255)
	 */
	protected $path;

	/**
	 * @var string
	 * @orm:Column(name="template", type="text")
	 */
	protected $template = '';

	/**
	 * @var string
	 * @orm:Column(name="template_compiled", type="text")
	 */
	protected $template_compiled = '';

	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * @var \DateTime
	 * @orm:Column(name="updated_at",type="datetime")
	 */
	protected $updated_at;

	public function __construct()
	{
		$this->created_at = new \DateTime();
		$this->updated_at = new \DateTime();
	}

	public function setTemplate($code)
	{
		$this->template = $code;

		// Erase compiled code when we update the tpl,
		// so it'll be updated when next rendered
		$this->template_compiled = '';
	}

	public function setStyleId($style_id)
	{
		if ($style_id) {
			$this->style = App::getEntityRepository('DeskPRO:Style')->find($style_id);
		} else {
			$this->style = null;
		}
	}

	public function getStyleId()
	{
		return $this->style ? $this->style['id'] : 0;
	}

	/** @orm:PreUpdate */
	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}