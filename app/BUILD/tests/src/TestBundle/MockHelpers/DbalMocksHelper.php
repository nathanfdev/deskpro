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

/**
 * DeskPRO.
 */
namespace DpTestSrc\TestBundle\MockHelpers;

use Doctrine\ORM\AbstractQuery as Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Prophecy\Argument;

/**
 * Trait DbalMocksHelper.
 *
 * Trait containing DBAL/ORM mock helpers
 */
trait DbalMocksHelper
{
    /**
     * @todo remove
     *
     * @param string $class
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockQueryBuildingEntityManager($class = EntityManager::class)
    {
        return $this->mockEntityManager($class);
    }

    /**
     * @param string $class
     *
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockEntityManager($class = EntityManager::class)
    {
        $em = $this->prophesize($class);
        $em->getRepository(Argument::any())->willReturn($this->mockRepository());
        $em->createQueryBuilder()->willReturn($this->mockQueryBuilder());

        return $em;
    }

    /**
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockQueryBuilder()
    {
        $query = $this->prophesize(Query::class);
        $qb    = $this->prophesize(QueryBuilder::class);

        // describe Query double
        $query->getArrayResult()->willReturn([]);
        $query->getSingleScalarResult()->willReturn(42);
        $query->getResult()->willReturn([]);
        $query->execute()->willReturn([]);

        // describe QueryBuilder double
        $qb->getQuery()->willReturn($query);
        $qb->getRootAliases()->willReturn(['alias']);
        $qb->select(Argument::any())->willReturn($qb);
        $qb->update(Argument::any(), Argument::any())->willReturn($qb);
        $qb->set(Argument::any(), Argument::any())->willReturn($qb);
        $qb->addSelect(Argument::any())->willReturn($qb);
        $qb->from(Argument::any(), Argument::any())->willReturn($qb);
        $qb->join(Argument::any(), Argument::any())->willReturn($qb);
        $qb->leftJoin(Argument::any(), Argument::any())->willReturn($qb);
        $qb->where(Argument::any())->willReturn($qb);
        $qb->andWhere(Argument::any())->willReturn($qb);
        $qb->groupBy(Argument::any())->willReturn($qb);
        $qb->setParameters(Argument::any())->willReturn($qb);
        $qb->orderBy(Argument::type('string'), Argument::type('string'))->willReturn($qb);
        $qb->expr()->willReturn(new \Doctrine\ORM\Query\Expr());

        return $qb;
    }

    /**
     * @return \Prophecy\Prophecy\ObjectProphecy
     */
    protected function mockRepository()
    {
        $repository = $this->prophesize(EntityRepository::class);
        $repository->findAll()->willReturn([]);

        return $repository;
    }
}
