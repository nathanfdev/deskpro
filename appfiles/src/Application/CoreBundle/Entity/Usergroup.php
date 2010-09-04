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

namespace Application\CoreBundle\Entity;
use Orb\Util\Strings;
use Orb\Util\Arrays;

/**
 * A usergroup is any way to group related users together. Not necessarily just for permissions.
 *
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="usergroups")
 */
class Usergroup extends \DeskPRO\Domain\DomainObject
{
	/**
	 * The unique ID.
	 *
	 * @var int
	 * @Id
	 * @Column(name="id", type="integer")
	 * @GeneratedValue
	 */
	protected $id = null;


	/**
	 * Title of the usergroup
	 *
	 * @var string
	 * @Column(name="title", type="string", length=255)
	 */
	protected $title;


	/**
	 * A note or description about the usergroup
	 *
	 * @var string
	 * @Column(name="note", type="text")
	 */
	protected $note = '';


	/**
	 * Properties attached to this usergroup
	 *
	 * @var Application\CoreBundle\Entity\UsergroupProperty
	 * @OneToOne(targetEntity="UsergroupProperty", mappedBy="usergroup")
	 */
	protected $properties;


	public function init(array $params = array())
	{
		$this->properties = new \Doctrine\Common\Collections\ArrayCollection();
	}

	public function fromArray(array $values)
	{
		if (isset($values['permissions']) AND is_array($values['permissions'])) {
			$em = $this->getContainer()->get('doctrine.orm.entity_manager');
			foreach ($values['permissions'] as $name => $val) {
				$prop = $em->createEntity('CoreBundle:UsergroupPropertyPermission');
				$prop['name'] = $name;
				if (is_bool($val)) {
					$prop['flag'] = $val;
				} else {
					$prop['data'] = $val;
				}

				$this->properties->add($prop);
			}

			unset($values['permissions']);
		}

		parent::fromArray($values);
	}
}