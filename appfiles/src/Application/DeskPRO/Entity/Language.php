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

use \Application\DeskPRO\App;

use \Symfony\Component\Validator\Constraints;
use \Symfony\Component\Validator\Mapping\ClassMetadata;

use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A language groups phrases and defines a locale code.
 *
 * @ORM_Mapping\Entity(repositoryClass="Application\DeskPRO\EntityRepository\Language")
 * @ORM_Mapping\Table(name="languages")
 */
class Language extends \Application\DeskPRO\Domain\DomainObject
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
	 * @ORM_Mapping\ManyToOne(targetEntity="Language")
	 * @ORM_Mapping\JoinColumn(name="parent_id", referencedColumnName="id", onDelete="cascade")
	 */
	protected $parent = null;

	/**
	 * Title of the language
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="title", type="string", length=255)
	 */
	protected $title;

	/**
	 * The language pack used with this lang
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="language_package", type="string", length=255)
	 */
	protected $language_package = '';

	/**
	 * The locale code
	 *
	 * @var string
	 * @ORM_Mapping\Column(name="locale", type="string", length=8)
	 */
	protected $locale = 'en_US';

	/**
	 * @var array
	 */
	protected $_all_parent_ids = null;


	public function setParentId($parent_id)
	{
		if ($this->id) {
			throw new \BadMethodCallException('You cannot change the parent_id once the record has been created. Hierarchy is fixed.');
		}

		$this->parent = App::getEntityRepository('DeskPRO:Language')->find($parent_id);
	}

	public function getParentId()
	{
		if ($this->parent) {
			return $this->parent['id'];
		}

		return 0;
	}

	/**
	 * Get an array of all parent IDs up the hierarchy, from closest parent to furthest. 0/null is not included.
	 *
	 * @return array
	 */
	public function getAllParentIds()
	{
		if ($this->_all_parent_ids !== null) return $this->_all_parent_ids;

		$this->_all_parent_ids = array();
		if ($this->parent) {
			$this->_all_parent_ids[] = $this->parent['id'];
			$this->_all_parent_ids = $this->_all_parent_ids + $this->parent->getAllParentIds();
		}

		return $this->_all_parent_ids;
	}
}
