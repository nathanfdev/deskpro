<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\EmailAccount;

/**
 * Email account record mapper.
 *
 * Class EmailAccount
 */
class EmailAccountMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return EmailAccount::class;
    }

    /**
     * @param string $email
     *
     * @return EmailAccount
     */
    public function findOneByEmail($email)
    {
        return $this->findOneBy(['address' => $email]);
    }
}
