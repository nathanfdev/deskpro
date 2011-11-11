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

use Application\DeskPRO\App;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Settings used by the system.
 *
 * @ORM_Mapping\Entity
 * @ORM_Mapping\Table(name="styles")
 */
class Style extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @ORM_Mapping\Id @ORM_Mapping\generatedValue(strategy="IDENTITY")
	 * @ORM_Mapping\Column(name="id", type="integer")
	 *
	 */
	protected $id = null;

	/**
	 * @var Style
	 * @ORM_Mapping\ManyToOne(targetEntity="Style")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent;

	/**
	 * Title of the style
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * A note or description about the style
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="note", type="text")
	 */
	protected $note = '';

	/**
	 * The blob containing the logo for this style.
	 * Later we'll allow multiple resources to be attached to styles, but for now the logo is
	 * here.
	 *
	 * @var \Application\DeskPRO\Entity\Blob
	 * @ORM_Mapping\OneToOne(targetEntity="Blob", fetch="EAGER")
	 * @ORM_Mapping\JoinColumn(name="logo_blob_id", referencedColumnName="id", onDelete="set null")
	 */
	protected $logo_blob_id = null;

	/**
	 * CSS dir under static with CSS files
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="css_dir", type="string", length=255)
	 */
	protected $css_dir = '';

	/**
	 * Last time the CSS variable was updated.
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="css_updated",type="datetime")
	 */
	protected $css_updated;

	/**
	 * Options for the style
	 *
	 * @var array
	 * @ORM_Mapping\Column(name="options", type="array")
	 */
	protected $options = array();

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	public function __construct()
	{
		$this->created_at = new \DateTime();
		$this->css_updated = new \DateTime();
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
