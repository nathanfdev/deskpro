<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\PersonEmail;

/**
 * Person email record mapper.
 *
 * Class PersonEmail
 */
class PersonEmailMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return PersonEmail::class;
    }

    /**
     * Returns person email entities.
     *
     * @param string $email
     *
     * @return \Application\DeskPRO\Entity\PersonEmail
     */
    public function findOneByEmail($email)
    {
        return $this->findOneBy(['email' => $email]);
    }
}
