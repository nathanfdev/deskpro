<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Doctrine\ORM\EntityManager;

/**
 * Class AbstractMapper.
 */
abstract class AbstractEntityManagerMapper implements MapperInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->em->getRepository($this->getEntityClass())->findOneBy($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        return $this->em->getRepository($this->getEntityClass())->find($id);
    }
}
