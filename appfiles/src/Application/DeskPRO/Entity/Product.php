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
 * Products
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Product")
 * @ORM_Mapping\Table(name="products")
 */
class Product extends CategoryAbstract
{
	/**
	 * @gedmo:TreeParent
	 * @ORM_Mapping\ManyToOne(targetEntity="Product", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @ORM_Mapping\OneToMany(targetEntity="Product", mappedBy="parent")
	 * @ORM_Mapping\OrderBy({"lft" = "ASC"})
	 */
	protected $children;
}