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

namespace DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;

use DeskPRO\Bundle\AppBundle\ObjectAlias;
use Doctrine\ORM\EntityRepository;

class Repository extends EntityRepository implements ObjectAlias\ObjectIdResolver
{
    /**
     * Returns a list of objects ids which are aliased( referenced ) by an app instance
     *
     * @param string $id app instance id
     * @return array|string[]
     */
    public function getAliasedObjectIdsByAppInstance($id)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('o.id')
            ->innerJoin('a.object', 'o')
            ->innerJoin('a.appInstance', 'app')
            ->andWhere('app.id = :appid')
            ->setParameter('appid', $id)
        ;
        $result = $qb->getQuery()->getScalarResult();

        return array_map(
            function (array $row) {
                return $row['id'];
            },
            $result
        );
    }

    /**
     * @param $alias
     * @return mixed|null
     */
    public function aliasExists( $alias )
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('COUNT(a.id)')
            ->where('a.alias = :alias')
            ->setParameter('alias', $alias)
        ;

        $count = $qb->getQuery()->getSingleScalarResult();
        return 1 === $count;
    }

    /**
     * @return string
     */
    function getObjectType()
    {
        return $this->getEntityName();
    }

    /**
     * @param ObjectAlias\QualifiedName $alias
     * @return \Doctrine\ORM\Query
     */
    public function buildResolveAliasQuery(ObjectAlias\QualifiedName $alias)
    {
        $qb = $this->createQueryBuilder('a');
        $qb
            ->select('o.id')
            ->innerJoin('a.object', 'o')
            ->where('a.alias = :alias')
            ->setParameter('alias', ObjectAlias\Converters::toStringFromName($alias))
        ;


        return $qb->getQuery();
    }

    /**
     * @param $alias
     * @return null|string
     */
    function resolveAlias( ObjectAlias\QualifiedName $alias )
    {
        $query = $this->buildResolveAliasQuery($alias);
        $result = $query->setMaxResults(2)->getScalarResult();
        if (1 === count($result)) {
            return (string) $result[0]['id'];
        }

        return null;
    }
}
