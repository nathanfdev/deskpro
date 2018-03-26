<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Organization;

/**
 * Organization record mapper.
 *
 * Class Organization
 */
class OrganizationMapper extends AbstractContainerMapper implements MapperByTitleInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Organization::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByTitle($title)
    {
        /** @var \Application\DeskPRO\EntityRepository\Organization $repository */
        $repository = $this->em->getRepository(Organization::class);

        return $repository->findOneByName($title);
    }
}
