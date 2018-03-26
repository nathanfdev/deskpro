<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

/**
 * Class AbstractCategoryMapper.
 */
abstract class AbstractCategoryMapper extends AbstractContainerMapper implements CategoryMapperInterface
{
    /**
     * {@inheritdoc}
     */
    public function findOneByTitle($title, $parentId = null)
    {
        return $this->findOneBy([
            'title'  => $title,
            'parent' => $parentId,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultCategory()
    {
        return $this->em->getRepository(static::getMapperEntityClass())->findOneBy([]);
    }
}
