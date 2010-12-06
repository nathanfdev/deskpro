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

namespace Application\DeskPRO\Auth\Adapter;

use \Orb\Auth\Result;



/**
 * The Local adapter handles local logins using an email address or username and a password.
 */
class Local implements \Orb\Auth\Adapter\AdapterInterface
{
	/**
	 * Entity manager
	 * @var Doctrine\ORM\EntityManager
	 */
	protected $em;

	protected $email = '';
	protected $password = '';

	public function __construct(\Doctrine\ORM\EntityManager $em)
	{
		$this->em = $em;
	}

	public function setCredentials($email, $password)
	{
		$this->email = $email;
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
		$qb->select('p')
			->from('DeskPRO:Person', 'p')
			->leftJoin('p.emails', 'e')
			->where('p.is_user = 1')
			->setMaxResults(1);

		$qb->andWhere('e.email = ?2');
		$qb->setParameter(2, $this->email);

		$person = null;

		try {
			$person = $qb->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {}

		if (!$person OR !$person->checkPassword($this->password)) {
			return new Result(Result::FAILURE_INVALID_CREDS);
		}

		$identity = new \Orb\Auth\Identity($person['id'], array('person' => $person));
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}
}