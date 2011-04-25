<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Tickets
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\People\PermissionLoader;

/**
 * An interface for category-based permissions
 */
interface CategoryPermissionsInterface
{
	/**
	 * Check if a specific category is allowed
	 *
	 * @param string $id
	 * @return bool
	 */
	public function isCategoryAllowed($id);


	/**
	 * Get an array of allowed category IDs.
	 * 
	 * @return array
	 */
	public function getAllowedCategories();


	/**
	 * Get an array of disallowed category IDs.
	 *
	 * @return array
	 */
	public function getDisallowedCategories();
}