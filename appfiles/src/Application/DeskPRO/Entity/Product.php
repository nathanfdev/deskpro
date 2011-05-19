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

/**
 * Products
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Product")
 * @orm:Table(name="products")
 */
class Product extends CategoryAbstract
{
	/**
	 * @gedmo:TreeParent
	 * @orm:ManyToOne(targetEntity="Product", inversedBy="children")
	 */
	protected $parent;

	/**
	 * @orm:OneToMany(targetEntity="Product", mappedBy="parent")
	 * @orm:OrderBy({"lft" = "ASC"})
	 */
	protected $children;
}