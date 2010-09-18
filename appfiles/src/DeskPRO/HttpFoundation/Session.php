<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category HttpFoundation
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\HttpFoundation;

use Orb\Util\Strings;
use Orb\Util\Util;

/**
 * Session is able to load up a user, their locale etc.
 */
class Session extends \Symfony\Component\HttpFoundation\Session
{
	/**
	 * The entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;

	/**
	 * The person this session belongs to
	 * @var Application\CoreBundle\Entity\Person
	 */
	protected $person;

	/**
	 * The language used for this user
	 * @var Application\CoreBundle\Entity\Language
	 */
	protected $language;

	public function __construct(\DeskPRO\ORM\EntityManager $em, SessionStorageInterface $storage, array $options = array())
	{
		parent::__construct($storage, $options);
		$this->em = $em;
	}


	
	/**
	 * Get the logged in Person
	 * 
	 * @return Application\CoreBundle\Entity\Person
	 */
	public function getPerson()
	{
		if ($this->person !== null) return $this->person;

		$person_id = $this->get('auth_person_id');
		$person = false;

		if ($person_id) {
			try {
				$person = $this->em->find('CoreBundle:Person', $person_id);
			} catch (\Doctrine\ORM\NoResultException $e) {}
		}

		if (!$person) {
			$person = $this->em->createEntity('CoreBundle:PersonGuest');
		}

		$this->person = $person;

		return $person;
	}


	
	/**
	 * Get the locale the user wants.
	 *
	 * @return string
	 */
	public function getLocale()
	{
		$language = $this->getLanguage();

		if (!$language) {
			return 'en_US';
		} else {
			return $language['locale'];
		}
	}


	/**
	 * Get the language the user wants.
	 *
	 * TODO: Currently there is no Language object for default (0) where phrases
	 * etc are gathered from filesystem. Perhaps this needs to change, or maybe
	 * make a LanguageDefault subclass like we did with PersonGuest.
	 *
	 * @return Application\CoreBundle\Entity\Language
	 */
	public function getLanguage()
	{
		if ($this->language !== null) return $this->language;

		$person = $this->getPerson();
		if ($person['language']) {
			$this->language = $person['language'];
		} else {
			$this->language = 0;
		}
	}
}