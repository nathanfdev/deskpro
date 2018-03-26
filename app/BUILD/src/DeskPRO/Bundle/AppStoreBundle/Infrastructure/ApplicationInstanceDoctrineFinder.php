<?php

namespace DeskPRO\Bundle\AppStoreBundle\Infrastructure;

use DeskPRO\Bundle\AppBundle\Entity;
use DeskPRO\Bundle\AppStoreBundle\Domain;
use Doctrine\ORM;

class ApplicationInstanceDoctrineFinder implements Domain\ApplicationInstanceFinder
{
    /** @var ORM\EntityManager */
    private $entityManager;

    /** @var EntityQueryBuilders */
    private $queryBuilder;

    /**
     * @param ORM\EntityManager $entityManager
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

    public function findSoleApplicationInstanceByRef( ApplicationRef $appRef) {
        $query = null;
        $id = $appRef->getIdentifier();
        if ($appRef->isName()) {
            $query = $this->queryBuilder->buildFindApplicationInstanceByNameQuery($this->entityManager, $id);
        } else {
            $query = $this->queryBuilder->buildFindApplicationInstanceByAppId($this->entityManager, $id);
        }

        $result = $query->setMaxResults(2)->getResult();

        /** @var Entity\AppStore\AppInstance $instance */
        $instance = null;
        if (1 === count($result)) { // must find exactly one
            $instance = array_pop($result);
        }

        return $instance;
    }

    /**
     * @param string $applicationName
     * @return mixed
     */
    function findSoleApplicationInstance($applicationName)
    {
        return $this->findSoleApplicationInstanceByRef(new ApplicationRef($applicationName, true));
    }

    function findAll()
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->from(Entity\AppStore\AppInstance::class, 'i')
            ->select('i')
            ->innerJoin('i.app', 'a')
        ;

        $result = $qb->getQuery()->getResult();
        return $result;
    }

    function findByFilter(Domain\SearchApplicationInstanceFilter $filter)
    {
        if ($filter->isEmpty()) {
            return [];
        }

        $query = $this->queryBuilder->buildFindApplicationInstanceByFilterQuery($this->entityManager, $filter);
        $result = $query->getResult();
        return $result;
    }

    /**
     * @param string $id
     * @return mixed
     */
    function findById($id)
    {
        $repository = $this->entityManager->getRepository(Entity\AppStore\AppInstance::class);
        /** @var Entity\AppStore\AppInstance $instance */
        $instance = $repository->find($id);
        return $instance;
    }
}
