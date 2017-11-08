<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Application\DeskPRO\Entity\Department;
use Application\ImportBundle\Writer\EntityPersister;
use Application\ImportBundle\Writer\Mapper\BrandMapper;
use Application\ImportBundle\Writer\Mapper\DepartmentMapper;
use Application\ImportBundle\Writer\Mapper\ImportMapMapper;
use Psr\Log\LoggerInterface;

/**
 * Class DepartmentHelper.
 */
class DepartmentHelper
{
    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var BrandMapper
     */
    private $brandMapper;

    /**
     * @var DepartmentMapper
     */
    private $departmentMapper;

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
     * @param ImportMapMapper  $importMapMapper
     * @param BrandMapper      $brandMapper
     * @param DepartmentMapper $departmentMapper
     * @param EntityPersister  $persister
     * @param LoggerInterface  $logger
     */
    public function __construct(
        ImportMapMapper  $importMapMapper,
        BrandMapper      $brandMapper,
        DepartmentMapper $departmentMapper,
        EntityPersister  $persister,
        LoggerInterface  $logger
    ) {
        $this->importMapMapper  = $importMapMapper;
        $this->brandMapper      = $brandMapper;
        $this->departmentMapper = $departmentMapper;
        $this->persister        = $persister;
        $this->logger           = $logger;
    }

    /**
     * Returns a department by title
     * Creates a new department if not found.
     *
     * @param string $type
     * @param string $departmentPath
     *
     * @return Department
     */
    public function findOrCreateDepartment($type, $departmentPath)
    {
        if (!$departmentPath) {
            throw new \RuntimeException('Department path is empty');
        }
        if (!is_string($departmentPath)) {
            throw new \RuntimeException('Department path expected to be a string');
        }
        if (!in_array($type, ['ticket', 'chat'])) {
            throw new \RuntimeException('Unknown department type, expected `ticket` or `chat`');
        }

        $departmentPath = explode('>', $departmentPath, 2);
        $departmentPath = array_map('trim', $departmentPath);

        /** @var Department $parent */
        $parent = null;
        $entity = null;
        foreach ($departmentPath as $title) {
            $entity = $this->departmentMapper->findOneBy([
                'title'              => $title,
                'parent'             => $parent ? $parent->getId() : null,
                'is_tickets_enabled' => $type === 'ticket',
                'is_chat_enabled'    => $type === 'chat',
            ]);

            if ($entity) {
                $this->logger->debug("Found existing {$type} department `$title`");
            } else {
                $this->logger->notice("Create a new {$type} department `$title`");

                if ($type === 'ticket') {
                    $entity = Department::createTicketDepartment();
                } elseif ($type === 'chat') {
                    $entity = Department::createChatDepartment();
                }

                $entity->setRealTitle($title);
                $entity->setParent($parent);

                if ($parent) {
                    $parent->getChildren()->add($entity);
                }

                $defaultBrand = $this->brandMapper->getDefaultBrand();
                if ($defaultBrand) {
                    $entity->addBrand($defaultBrand);
                }

                $this->persister->persistAndFlush($entity);
            }

            $parent = $entity;
        }

        return $entity;
    }
}
