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

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use \Orb\Util\Strings;
use \Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @orm:Entity
 * @orm:Table(name="styles")
 */
class Style extends \Application\DeskPRO\Domain\DomainObject
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
	 * @var Style
	 * @orm:ManyToOne(targetEntity="Style")
	 * @orm:JoinColumn(name="parent_id", referencedColumnName="id")
	 */
	protected $parent;

	/**
	 * Title of the style
	 *
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * A note or description about the style
	 *
	 * @var string
	 * @orm:Column(name="note", type="text")
	 */
	protected $note;

	/**
	 * @var \DateTime
	 * @orm:Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	public function __construct()
	{
		$this->created_at = new \DateTime();
	}

	public function setParentId($parent_id)
	{
		if ($parent_id) {
			$this->parent = App::getEntityRepository('DeskPRO:Style')->find($parent_id);
		} else {
			$this->parent = null;
		}
	}

	public function getParentId()
	{
		return $this->parent ? $this->parent['id'] : 0;
	}


	public function getTemplate($template_name)
	{
		return App::getEntityRepository('DeskPRO:Template')->getTemplateForStyle($template_name, $this);
	}

	public function getTemplateObject($template_name)
	{
		$tpl = $this->getTemplate($template_name);
		if (!$tpl) {
			$tpl = new Template();
			$tpl['path'] = $template_name;
			$tpl['style'] = $this;
		}

		return $tpl;
	}

	public function getCustomTemplateNames()
	{
		return App::getEntityRepository('DeskPRO:Template')->getCustomTemplateNamesInStyle($this);
	}
}