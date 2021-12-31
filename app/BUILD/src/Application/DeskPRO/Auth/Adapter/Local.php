<?php

namespace Application\DeskPRO\Auth\Adapter;

use Application\DeskPRO\People\PasswordPolicyValidator;
use Application\DeskPRO\Usersource\Adapter\EntityManagerAwareInterface;
use DeskPRO\Bundle\PortalBundle\Routing\PasswordResetException;
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
     * @var bool
     */
    protected $useUniqueEmail;

    /**
     * @var Logger
     */
    protected $logger;

    /** @var string */
    protected $email = '';
    /** @var string */
    protected $password = '';

    public function __construct(EntityManager $em, $useUniqueEmail = true)
    {
        $this->em             = $em;
        $this->useUniqueEmail = $useUniqueEmail;
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
     * @throws PasswordResetException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return Result
     */
    public function doAuthenticate()
    {
        $timeStart = microtime(true);
        if ($this->logger) {
            $this->logger->log('START Local::authenticate', Logger::DEBUG);
            $this->logger->log("Request: {$this->email}:{$this->password}", Logger::DEBUG);
        }

        $qb = $this->em->createQueryBuilder();
        $qb->select('p')
            ->from('DeskPRO:Person', 'p')
            ->leftJoin('p.emails', 'e')
            ->where('p.is_user = 1 AND p.is_deleted = 0')
            ->andWhere('e.email = ?2')
            ->setParameter(2, $this->email)
        ;

        $matchedPeople = [];

        if ($this->useUniqueEmail) {
            $qb->setMaxResults(1);

            $person = null;

            try {
                /** @var \Application\DeskPRO\Entity\Person $person */
                $person = $qb->getQuery()->getSingleResult();
            } catch (\Doctrine\ORM\NoResultException $e) {
            }
            $this->logPeopleFound([$person], $timeStart);

            if ($person && $person->checkPassword($this->password)) {
                $matchedPeople[] = $person;
            }
        } else {
            $people = $qb->getQuery()->getResult();
            $this->logPeopleFound($people, $timeStart);

            foreach ($people as $checkPerson) {
                /** @var \Application\DeskPRO\Entity\Person $checkPerson */
                if ($checkPerson->checkPassword($this->password)) {
                    $matchedPeople[] = $checkPerson;
                }
            }
        }

        return $this->processMatchedPeopleAuthentication($matchedPeople);
    }

    /**
     * @param array $matchedPeople
     *
     * @throws PasswordResetException
     *
     * @return Result
     */
    protected function processMatchedPeopleAuthentication(array $matchedPeople)
    {
        // means no people with matching email and password
        $matchedPeopleCount = count($matchedPeople);
        if ($matchedPeopleCount < 1) {
            return new Result(Result::FAILURE_INVALID_CREDS);
        } elseif ($matchedPeopleCount === 1) {
            $person = $matchedPeople[0];

            if ($person->date_password_set && $person->date_password_set->format('Y-m-d H:i:s') === PasswordPolicyValidator::MAGIC_PASSWORD_RESET_REQUIRED) {
                throw new PasswordResetException($person);
            }
        } else {
            return new Result(Result::MULTIPLE_MATCHES, null, [
                Result::MSG_IDENTITIES => array_map(
                    function ($p) {
                        return [
                            'id'    => $p->getId(),
                            'email' => $p->getEmail(),
                            'keys'  => [],
                        ];
                    },
                    $matchedPeople
                ),
            ]);
        }

        $identity = new Identity(
            $person['id'],
            [
                'email'           => $person->primary_email->email,
                'email_confirmed' => true,
            ]
        );
        $identity->setFriendlyIdentity($person->primary_email->email);

        return new Result(Result::SUCCESS, $identity);
    }

    protected function logPeopleFound(array $people, $timeStart)
    {
        if ($this->logger) {
            $peopleCount = count($people);
            if ($peopleCount > 1) {
                $this->logger->log(
                    sprintf(
                        'Found %d people. IDs are: %s',
                        $peopleCount,
                        implode(',', array_map(function ($p) {
                            return $p->getId();
                        }, $people))
                    ),
                    Logger::DEBUG
                );
            } elseif ($peopleCount === 1) {
                $person = $people[0];
                $this->logger->log('Found user '.$person->getId(), Logger::DEBUG);
            } else {
                $this->logger->log('No user found', Logger::DEBUG);
            }

            $this->logger->log(
                sprintf('END Local::authenticate (took %.4fs)', microtime(true) - $timeStart), Logger::DEBUG
            );
        }
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
