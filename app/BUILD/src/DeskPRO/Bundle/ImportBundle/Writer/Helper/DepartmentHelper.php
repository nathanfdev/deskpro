<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\DepartmentMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
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
     * @var BrandHelper
     */
    private $brandHelper;

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
     * @param BrandHelper      $brandHelper
     * @param DepartmentMapper $departmentMapper
     * @param EntityPersister  $persister
     * @param LoggerInterface  $logger
     */
    public function __construct(
        ImportMapMapper  $importMapMapper,
        BrandHelper      $brandHelper,
        DepartmentMapper $departmentMapper,
        EntityPersister  $persister,
        LoggerInterface  $logger
    ) {
        $this->importMapMapper  = $importMapMapper;
        $this->brandHelper      = $brandHelper;
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
     * @param string $brandName
     *
     * @return Department
     */
    public function findOrCreateDepartment($type, $departmentPath, $brandName = null)
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

                if ($brandName && $brand = $this->brandHelper->findOrCreateBrand($brandName)) {
                    $entity->addBrand($brand);
                } else {
                    $defaultBrand = $this->brandHelper->getDefaultBrand();
                    if ($defaultBrand) {
                        $entity->addBrand($defaultBrand);
                    }
                }

                $this->persister->persistAndFlush($entity);
            }

            $parent = $entity;
        }

        return $entity;
    }
}
