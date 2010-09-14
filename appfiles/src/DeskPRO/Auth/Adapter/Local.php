<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Auth
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris@nadeau.ws>
 */

namespace DeskPRO\Auth\Adapter;

use \Orb\Auth\Result;



/**
 * The Local adapter handles local logins using an email address or username and a password.
 */
class Local implements AdapterInterface
{
	/**
	 * Entity manager
	 * @var DeskPRO\ORM\EntityManager
	 */
	protected $em;

	protected $username_or_email = '';
	protected $password = '';

	public function __construct(\DeskPRO\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	public function setCredentials($username_or_email, $password)
	{
		$this->username_or_email = $username_or_email;
		$this->password = $password;
	}

	/**
	 * Authenticate a user.
	 *
	 * @return
	 */
	public function authenticate()
	{
		$qb = $this->em->createQueryBuilder();
		$qb->select('p.*')
			->from('CoreBundle:Person', 'p')
			->leftJoin('p.email_addresses', 'e')
			->where('p.is_user = 1 AND p.username = ?1')
			->setMaxResults(1);
		$qb->setParameter(1, $this->username_or_email);

		if (strpos($this->username_or_email, '@')) {
			$qb->orWhere('e.email_address = ?2');
			$qb->setParameter(2, $this->username_or_email);
		}

		$person = $qb->getQuery()->getFirstResult();
		if (!$person OR !$person->checkPassword($this->password)) {
			return new Result(Result::FAILURE_INVALID_CREDS);
		}

		$identity = new \Orb\Auth\Identity($person['id'], array('person' => $person));
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}
}