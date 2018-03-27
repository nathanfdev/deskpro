<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain\Application;
use DeskPRO\Bundle\AppStoreBundle\Domain\ApplicationFinder;
use Doctrine\ORM;

class ApplicationDoctrineFinder implements ApplicationFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var EntityQueryBuilders */
    private $queryBuilder;

    /**
     * @param ORM\EntityManager   $entityManager
     * @param EntityQueryBuilders $queryBuilder
     */
    public function __construct(ORM\EntityManager $entityManager, EntityQueryBuilders $queryBuilder = null)
    {
        $this->entityManager = $entityManager;
        if (is_null($queryBuilder)) {
            $this->queryBuilder = new EntityQueryBuilders();
        } else {
            $this->queryBuilder = $queryBuilder;
        }
    }

    public function findAll()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
        ;

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function findAllById($idList)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
            ->where('a.id IN (:idList)')->setParameter('idList', $idList)
        ;

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    public function findByReference(ApplicationRef $reference)
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\App::class, 'a')
            ->select('a')
        ;

        $id = $reference->getIdentifier();
        if ($reference->isName()) {
            $qb->where('a.name = :name')->setParameter('name', $id);
        } elseif ($reference->isId()) {
            $qb->where('a.id = :id')->setParameter('id', $id);
        } else {
            throw new \DomainException('unknown application reference');
        }

        $result = $qb->getQuery()->getResult();
        if (1 != count($result)) { //instance not found or more than one
            return null;
        }

        /** @var Entity\AppStore\App $instance */
        $instance = array_pop($result);

        return $instance;
    }

    /**
     * @param string $name
     *
     * @return Entity\AppStore\App|null
     */
    public function findByName( $name)
    {
        return $this->findByReference(new ApplicationRef($name, true));
    }

    /**
     * @param string $id
     *
     * @return Entity\AppStore\App|null
     */
    public function findByInstanceId($id)
    {
        $query  = $this->queryBuilder->buildFindApplicationByInstanceIdQuery($this->entityManager, $id);
        $result = $query->setMaxResults(2)->getResult();

        /** @var Entity\AppStore\App $instance */
        $instance = null;
        if (1 === count($result)) { // must find exactly one
            $instance = array_pop($result);
        }

        return $instance;
    }
}
