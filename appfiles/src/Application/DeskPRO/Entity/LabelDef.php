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

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

use \Application\DeskPRO\App;

/**
 * These are pre-defined labels that are allowed to be used.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\LabelDef")
 * @ORM_Mapping\Table(name="label_defs")
 */
class LabelDef extends \Application\DeskPRO\Domain\DomainObject
{
	/**
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="label_type", type="string", length=50)
	 */
	protected $label_type;

	/**
	 * @var string
	 * @ORM_Mapping\Id
	 * @ORM_Mapping\Column(name="label", type="string", length=255)
	 */
	protected $label;

	/**
	 * Get the name of the entity used to store label associations for this type.
	 *
	 * @return string
	 */
	public function getLabelEntityName()
	{
		return App::getEntityRepository('DeskPRO:LabelDef')->getLabelEntityFromType($this->label_type);
	}

	/**
	 * Get the table name used to store label associations for this type.
	 *
	 * @return string 
	 */
	public function getLabelTable()
	{
		$ent = App::getEntityRepository('DeskPRO:LabelDef')->getLabelEntityFromType($this->label_type);
		$class = App::getEntityClass($ent);
		$table = $class::getTableName();

		return $table;
	}
}