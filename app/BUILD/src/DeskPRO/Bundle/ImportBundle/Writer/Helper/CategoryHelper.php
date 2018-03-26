<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\CategoryAbstract;
use Application\DeskPRO\Entity\ImportMap;
use Application\DeskPRO\Entity\TicketCategory;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\CategoryMapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryHelper.
 */
class CategoryHelper
{
    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var BrandHelper
     */
    private $brandHelper;

    /**
     * @var UserGroupHelper
     */
    private $userGroupHelper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ImportMapMapper $importMapMapper
     * @param BrandHelper     $brandHelper
     * @param UserGroupHelper $userGroupHelper
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImportMapMapper $importMapMapper,
        BrandHelper     $brandHelper,
        UserGroupHelper $userGroupHelper,
        EntityPersister $persister,
        LoggerInterface $logger
    ) {
        $this->importMapMapper = $importMapMapper;
        $this->brandHelper     = $brandHelper;
        $this->userGroupHelper = $userGroupHelper;
        $this->persister       = $persister;
        $this->logger          = $logger;
    }

    /**
     * @param CategoryMapperInterface $mapper
     * @param string                  $categoryPath
     * @param string                  $brandName
     *
     * @return CategoryAbstract
     */
    public function findOrCreateCategory(CategoryMapperInterface $mapper, $categoryPath, $brandName = null)
    {
        if (!$categoryPath) {
            throw new \RuntimeException('Category path is empty');
        }
        if (!is_string($categoryPath)) {
            throw new \RuntimeException('Category path expected to be a string');
        }

        // if it's numeric then try to get category by id
        if (is_int($categoryPath) || ctype_digit($categoryPath)) {
            $entity = null;

            $typename = $this->importMapMapper->getImportMapKey($mapper->getEntityClass());
            /** @var ImportMap $importMap */
            $importMap = $this->importMapMapper->findOneBy([
                'old_id'   => $categoryPath,
                'typename' => $typename,
            ]);

            if ($importMap) {
                $entity = $mapper->find($importMap->getNewId());
            }

            $categoryTitle = 'Category '.$categoryPath;
            if (!$entity) {
                // try to find a category by title
                $entity = $mapper->findOneByTitle($categoryTitle);
            }
            if (!$entity) {
                // if no category then create a new category by oid
                $categoryClass = $mapper->getEntityClass();

                /** @var CategoryAbstract $entity */
                $entity = new $categoryClass();
                $entity->setTitle($categoryTitle);
                $this->setDefaultBrand($entity, $brandName);
                $this->userGroupHelper->updateUserGroups($entity);

                $this->persister->persistAndFlush($entity);

                $importMap = new ImportMap();
                $importMap->setOldId($categoryPath);
                $importMap->setNewId($entity->getId());
                $importMap->setTypename($typename);

                $this->persister->persistAndFlush($importMap);
            }
        } else {
            $categoryPath = explode('>', $categoryPath);
            $categoryPath = array_map('trim', $categoryPath);

            /** @var CategoryAbstract $parent */
            $parent = null;
            $entity = null;
            foreach ($categoryPath as $categoryTitle) {
                $entity = $mapper->findOneByTitle($categoryTitle, $parent ? $parent->getId() : null);
                if ($entity) {
                    $this->logger->debug("Found existing category `$categoryTitle`");
                } else {
                    $this->logger->debug("Create a new category `$categoryTitle`");

                    $categoryClass = $mapper->getEntityClass();

                    /** @var CategoryAbstract $entity */
                    $entity = new $categoryClass();
                    $entity->setTitle($categoryTitle);
                    $entity->setParent($parent);
                    $this->setDefaultBrand($entity, $brandName);
                    $this->userGroupHelper->updateUserGroups($entity);

                    if ($parent) {
                        $parent->getChildren()->add($entity);
                    }

                    $this->persister->persistAndFlush($entity);
                }

                $parent = $entity;
            }
        }

        return $entity;
    }

    /**
     * @param CategoryAbstract|TicketCategory $entity
     * @param string                          $brandName
     */
    private function setDefaultBrand($entity, $brandName)
    {
        if (method_exists($entity, 'setBrand')) {
            $brand = null;

            // try to get custom brand
            if ($brandName) {
                // set specific brand for multi-brand helpdesks
                $brand = $this->brandHelper->findOrCreateBrand($brandName);
                if ($brand) {
                    $entity->setBrand($brand);
                }
            }

            // get default brand
            if (!$brand) {
                $brand = $this->brandHelper->getDefaultBrand();
            }

            $entity->setBrand($brand);
        }
    }
}
