<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Auth\Adapter;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Usersource\Adapter\EntityManagerAwareInterface;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Adapter\FormLoginInterface;
use Orb\Auth\Adapter\PluginAdapter;
use Orb\Auth\Identity;
use Orb\Auth\Result;
use Orb\Log\Loggable;
use Orb\Log\Logger;

/**
 * The Local adapter handles local logins using an email address or username and a password.
 */
class Local extends PluginAdapter implements FormLoginInterface, Loggable, EntityManagerAwareInterface
{
    /**
     * Entity manager.
     *
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var Logger
     */
    protected $logger;

    /** @var string */
    protected $email = '';
    /** @var string */
    protected $password = '';

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Sets the data got from a form.
     *
     * @param array $form_data
     */
    public function setFormData(array $form_data)
    {
        if (isset($form_data['username'])) {
            $identifier = $form_data['username'];
        } elseif (isset($form_data['email'])) {
            $identifier = $form_data['email'];
        } else {
            $identifier = '';
        }

        $password = isset($form_data['password']) ? $form_data['password'] : '';

        $this->setCredentials($identifier, $password);
    }

    public function setCredentials($email, $password)
    {
        $this->email    = $email;
        $this->password = $password;
    }

    /**
     * Authenticate a user.
     *
     * @return
     */
    public function doAuthenticate()
    {
        $time_start = microtime(true);
        if ($this->logger) {
            $this->logger->log('START Local::authenticate', Logger::DEBUG);
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
        } catch (\Doctrine\ORM\NoResultException $e) {
        }

        if ($this->logger) {
            if ($person) {
                $this->logger->log('Found user '.$person->getId(), Logger::DEBUG);
            } else {
                $this->logger->log('No user found', Logger::DEBUG);
            }

            $this->logger->log(
                sprintf('END Local::authenticate (took %.4fs)', microtime(true) - $time_start), Logger::DEBUG
            );
        }

        if (!$person or !$person->checkPassword($this->password)) {
            return new Result(Result::FAILURE_INVALID_CREDS);
        }

        $identity = new Identity(
            $person['id'],
            [
                'email'           => $person->primary_email->email,
                'email_confirmed' => true,
            ]
        );
        $identity->setFriendlyIdentity($person->primary_email->email);
        $result = new Result(Result::SUCCESS, $identity);

        return $result;
    }

    /**
     * Set the logger.
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

    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }
}
