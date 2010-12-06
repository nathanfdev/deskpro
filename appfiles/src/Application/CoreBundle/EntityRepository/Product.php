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

namespace Application\CoreBundle\EntityRepository;

use \Application\DeskPRO\App;

use \Doctrine\ORM\EntityRepository;

class Product extends EntityRepository
{
	protected $product_names = null;

	/**
	 * @return array
	 */
	public function getProductNames()
	{
		if ($this->product_names !== null) return $this->product_names;

		$db = App::getDb();
		$this->product_names = $db->feetchAllKeyValue("
			SELECT id, title
			FROM products
			ORDER BY title ASC
		");

		return $this->product_names;
	}
}