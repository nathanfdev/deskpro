<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Usersource\Adapter;

use Application\DeskPRO\App;
use Application\DeskPRO\Auth\Adapter\Local;
use Application\DeskPRO\Usersource\UsersourceInfo;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;

/**
 * The local DeskPRO login usersource.
 */
class DeskPRO extends AbstractAdapter implements IdentityFinderInterface, EntityManagerAwareInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function getFieldsFromIdentity(Identity $identity)
    {
        return $identity->getRawData();
    }

    public function setEm(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @return EntityManager
     */
    public function getEm()
    {
        return $this->em ?: App::getContainer()->getEm();
    }

    public function findIdentityByInput($input)
    {
        /** @var \Application\DeskPRO\EntityRepository\Person $personRepo */
        $personRepo = $this->getEm()->getRepository('DeskPRO:Person');
        if ($person = $personRepo->findOneByEmail($input)) {
            return $person;
        }

        return;
    }

    /**
     * @return \Orb\Auth\Adapter\Local
     */
    protected function _createAuthAdapterObject()
    {
        return new Local($this->getEm());
    }

    /**
     * @return array
     */
    public function getCapabilities()
    {
        return [
            UsersourceInfo::CAPABILITY_FORM_LOGIN,
            UsersourceInfo::CAPABILITY_FIND_IDENTITY,
        ];
    }

    /**
     * @param mixed $capability
     *
     * @return bool
     */
    public function isCapable($capability)
    {
        return in_array($capability, $this->getCapabilities());
    }
}
