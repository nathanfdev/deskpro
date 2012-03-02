<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\Entity;

use Doctrine\ORM\Mapping as ORM_Mapping;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * Templates used in the system
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Template")
 * @ORM_Mapping\HasLifecycleCallbacks
 * @ORM_Mapping\Table(name="templates")
 */
class Template extends \Application\DeskPRO\Domain\DomainObject
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
	 * The style this template belongs to
	 *
	 * @var Style
	 * @ORM_Mapping\ManyToOne(targetEntity="Style")
	 * @ORM_Mapping\JoinColumn(name="style_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $style;

	/**
	 * The path of the template
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="path", type="string", length=255)
	 */
	protected $path;

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="template", type="text")
	 */
	protected $template = '';

	/**
	 * @var string
	 * @ORM_Mapping\Column(name="template_compiled", type="text")
	 */
	protected $template_compiled = '';

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="created_at",type="datetime")
	 */
	protected $created_at;

	/**
	 * @var \DateTime
	 * @ORM_Mapping\Column(name="updated_at",type="datetime")
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

	/** @ORM_Mapping\PreUpdate */
	public function _incUpdatedAt()
	{
		$this->updated_at = new \DateTime();
	}
}
