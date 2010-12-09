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
 * Departments
 *
 * @orm:Entity(repositoryClass="Application\DeskPRO\EntityRepository\Department")
 * @orm:Table(name="departments")
 */
class Department extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var int
	 * @orm:Id @orm:generatedValue(strategy="IDENTITY") @orm:Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id;

	/**
	 * @var string
	 * @orm:Column(name="title", type="string", length=255)
	 */
	protected $title;
}