<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Auth
 */

namespace Application\DeskPRO\Auth\Adapter;

use Doctrine\ORM\EntityManager;
use Orb\Auth\Adapter\AdapterInterface;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Logger;


/**
 * The Local adapter handles local logins using an email address or username and a password.
 */
class Local implements AdapterInterface, FormLoginInterface, Loggable
{
	/**
	 * Entity manager
	 * @var \Doctrine\ORM\EntityManager
	 */
	protected $em;

	/**
	 * @var Logger
	 */
	protected $logger;

	protected $email = '';
	protected $password = '';

	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}

	/**
	 * Sets the data got from a form
	 */
	public function setFormData(array $form_data)
	{
		$identifier = isset($form_data['username']) ? $form_data['username'] : $form_data['email'];
		$this->setCredentials($identifier, $form_data['password']);
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
		$time_start = microtime(true);
		if ($this->logger) {
			$this->logger->log("START Local::authenticate", Logger::DEBUG);
			$this->logger->log("Request: {$this->email}:{$this->password}", Logger::DEBUG);
		}

		$qb = $this->em->createQueryBuilder();
		$qb->select('p')
			->from('DeskPRO:Person', 'p')
			->leftJoin('p.emails', 'e')
			->where('p.is_user = 1 AND p.is_deleted = 0')
			->setMaxResults(1);

		$qb->andWhere('e.email = ?2');
		$qb->setParameter(2, $this->email);

		$person = null;

		try {
			/** @var \Application\DeskPRO\Entity\Person $person */
			$person = $qb->getQuery()->getSingleResult();
		} catch (\Doctrine\ORM\NoResultException $e) {}

		if ($this->logger) {

			if ($person) {
				$this->logger->log("Found user " . $person->getId(), Logger::DEBUG);
			} else {
				$this->logger->log("No user found", Logger::DEBUG);
			}

			$this->logger->log(
				sprintf("END Local::authenticate (took %.4fs)", microtime(true) - $time_start), Logger::DEBUG
			);
		}

		if (!$person OR !$person->checkPassword($this->password)) {
			return new Result(Result::FAILURE_INVALID_CREDS);
		}

		$identity = new Identity(
			$person['id'],
			array(
				'email' => $person->primary_email->email,
				'email_confirmed' => true
			)
		);
		$identity->setFriendlyIdentity($person->primary_email->email);
		$result = new Result(Result::SUCCESS, $identity);

		return $result;
	}


	/**
	 * Set the logger
	 *
	 * @param \Orb\Log\Logger $logger
	 */
	public function setLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return \Orb\Log\Logger
	 */
	public function getLogger()
	{
		return $this->logger;
	}
}
