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

/**
 * Idea categories
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\DownloadCategory")
 * @ORM_Mapping\Table(name="download_categories")
 */
class DownloadCategory extends CategoryAbstract
{
	/**
	 * @ORM_Mapping\ManyToOne(targetEntity="DownloadCategory", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="DownloadCategory", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"display_order" = "ASC"})
	 */
	protected $children;
}
