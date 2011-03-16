<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage RefGenerator
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace \Application\DeskPRO\RefGenerator;

interface RefGeneratorInterface
{

	/**
	 * Generates a new reference number for the supplied object type.
	 * Reference numbers must be at MOST 25 characters, and must be unique.
	 *
	 * @param  $object_type
	 * @return string
	 */
	public function generateReference($object_type);
}