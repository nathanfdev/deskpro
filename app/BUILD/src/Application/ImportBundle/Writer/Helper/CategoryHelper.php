<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace Application\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\CategoryAbstract;
use Application\ImportBundle\Writer\EntityPersister;
use Application\ImportBundle\Writer\Mapper\CategoryMapperInterface;
use Psr\Log\LoggerInterface;

/**
 * Class CategoryHelper.
 */
class CategoryHelper
{
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
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(EntityPersister $persister, LoggerInterface $logger)
    {
        $this->persister = $persister;
        $this->logger    = $logger;
    }

    /**
     * @param CategoryMapperInterface $mapper
     * @param string                  $categoryPath
     *
     * @return CategoryAbstract
     */
    public function findOrCreateCategory(CategoryMapperInterface $mapper, $categoryPath)
    {
        if (!$categoryPath) {
            throw new \RuntimeException('Category path is empty');
        }
        if (!is_string($categoryPath)) {
            throw new \RuntimeException('Category path expected to be a string');
        }

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

                if ($parent) {
                    $parent->getChildren()->add($entity);
                }

                $this->persister->persistAndFlush($entity);
            }

            $parent = $entity;
        }

        return $entity;
    }
}
