<?php

namespace DeskPRO\Bundle\AppBundle\Usersource;

use Application\DeskPRO\Entity\TmpData;
use Application\DeskPRO\Entity\Usersource;
use Doctrine\ORM\EntityManager;
use Orb\Auth\Identity;

/**
 * Temporararily saves a usersource response (Identity/Usersource combo) so that we can verify an email
 * before saving them to a Person object. Client code that detect we need to verify an email can use this
 * to save/retrieve usersource/identity information.
 */
class UsersourceIdentitySaver
{
    const TMP_DATA_TYPE = 'usersource_identity';

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Gets the Usersource used when the $tmp_auth was made.
     *
     * @param $tmp_auth
     *
     * @return \Application\DeskPRO\Entity\Usersource|null
     */
    public function fetchUsersource($tmp_auth)
    {
        if ($tmp = $this->getTmpByAuth($tmp_auth)) {
            return $this->getUsersourceById($tmp->getData('usersource_id'));
        }

        return;
    }

    /**
     * Gets the Identity from the usersource when the $tmp_auth was made.
     *
     * @param $tmp_auth
     *
     * @return \Orb\Auth\Identity
     */
    public function fetchIdentity($tmp_auth)
    {
        if ($tmp = $this->getTmpByAuth($tmp_auth)) {
            return $tmp->getData('identity');
        }

        return;
    }

    public function save(Usersource $usersource, Identity $identity)
    {
        $tmp = TmpData::create(self::TMP_DATA_TYPE, [
            'usersource_id' => $usersource->getId(),
            'identity'      => $identity,
        ]);

        $this->em->persist($tmp);
        $this->em->flush($tmp);

        return $tmp->getAuth();
    }

    private function saveTmpData(TmpData $tmp)
    {
        $this->em->persist($tmp);
        $this->em->flush($tmp);
    }

    /**
     * @param $auth
     *
     * @return null|\Application\DeskPRO\Entity\TmpData
     */
    private function getTmpByAuth($auth)
    {
        return $this->tmpDataRepo()->findOneBy(['auth' => $auth]);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Usersource
     */
    private function usersourceRepo()
    {
        return $this->em->getRepository('DeskPRO:Usersource');
    }

    /**
     * @return \Application\DeskPRO\Entity\Usersource|null
     */
    private function getUsersourceById($id)
    {
        return $this->em->getRepository('DeskPRO:Usersource')->find($id);
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\TmpData
     */
    private function tmpDataRepo()
    {
        return $this->em->getRepository('DeskPRO:TmpData');
    }
}
