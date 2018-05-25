<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\People;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Doctrine\ORM\EntityManager;

class UserRuleProcessor
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param \Doctrine\ORM\EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param Person $person
     */
    public function newRegister(Person $person)
    {
        $email = $person->getPrimaryEmail();

        if ($email) {
            $this->newEmail($person, $email);
        }
    }

    /**
     * @param Person $person
     */
    public function newContact(Person $person)
    {
        $email = $person->getPrimaryEmail();

        if ($email) {
            $this->newEmail($person, $email);
        }
    }

    /**
     * @param \Application\DeskPRO\Entity\Person      $person
     * @param \Application\DeskPRO\Entity\PersonEmail $email
     */
    public function newEmail(Person $person, PersonEmail $email)
    {
        $change        = false;
        $email_address = $email->email;
        $domain        = $email->getEmailDomain();

        $rules = $this->em->getRepository('DeskPRO:UserRule')->getMatching($email_address);
        if ($rules) {
            foreach ($rules as $r) {
                if ($r->add_usergroup) {
                    $change = true;
                    $person->addUsergroup($r->add_usergroup);
                }
                if ($r->add_organization && !$person->organization) {
                    $change = true;
                    $person->setOrganization($r->add_organization);
                }
            }
        }

        // And check orgs with domain assocs
        if (!$person->organization) {
            $orgem = $this->em->createQuery('
                SELECT od, org
                FROM DeskPRO:OrganizationEmailDomain od
                LEFT JOIN od.organization org
                WHERE od.domain = ?1
            ')->setParameter(1, $domain)->setMaxResults(1)->getOneOrNullResult();

            if ($orgem) {
                $change = true;
                $person->setOrganization($orgem->organization);
            }

            if ($change) {
                $this->em->persist($person);
            }
        }
    }
}
