<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Usergroup;

/**
 * User group record mapper.
 *
 * Class UserGroup
 */
class UserGroupMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Usergroup::class;
    }

    /**
     * Returns the DeskPRO record by sys name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function findOneBySysName($name)
    {
        return $this->findOneBy(['sys_name' => $name]);
    }

    /**
     * Returns the DeskPRO record by title.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function findOneByTitle($name)
    {
        return $this->findOneBy(['title' => $name]);
    }
}
