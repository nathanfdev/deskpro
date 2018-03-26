<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Department;

/**
 * Class DepartmentMapper.
 */
class DepartmentMapper extends AbstractContainerMapper
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Department::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultChatDepartment()
    {
        return $this->em->getRepository($this->getEntityClass())->getDefaultDepartment('chat');
    }
}
