<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Doctrine\ORM\EntityManager;

/**
 * Class CommonMapper.
 */
class CommonMapper extends AbstractEntityManagerMapper
{
    /**
     * @var string
     */
    private $entityClass;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     * @param string        $entityClass
     */
    public function __construct(EntityManager $em, $entityClass)
    {
        parent::__construct($em);
        $this->entityClass = $entityClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityClass()
    {
        return $this->entityClass;
    }
}
