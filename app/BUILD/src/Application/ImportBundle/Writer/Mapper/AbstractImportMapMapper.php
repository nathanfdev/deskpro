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

namespace Application\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model\ImportModelInterface;

/**
 * Abstract "import map" mapper.
 * Supports import bundle entity as criteria parameter.
 *
 * Class AbstractImportMapMapper
 */
abstract class AbstractImportMapMapper extends AbstractEntityManagerMapper
{
    /**
     * Returns new id by import map.
     *
     * @param array $criteria
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @return null|string
     */
    protected function findImportMapNewId(array $criteria)
    {
        if (isset($criteria['entity'])) {
            $entity = $criteria['entity'];
            if (!$entity instanceof ImportModelInterface) {
                throw new \RuntimeException('Criteria `entity` should be instance of '.ImportModelInterface::class);
            }

            $qb = $this->em->getRepository(DeskPROEntity\ImportMap::class)->createQueryBuilder('i');
            $qb
                ->select('i')
                ->andWhere($qb->expr()->eq('i.typename', '?0'))
                ->andWhere($qb->expr()->eq('i.old_id', '?1'))
                ->setParameters([
                    $entity->getImportMapKey(),
                    $entity->getOid(),
                ])
            ;

            /** @var DeskPROEntity\ImportMap $import_map */
            $import_map = $qb->getQuery()->getOneOrNullResult();
            if ($import_map) {
                return $import_map->getNewId();
            }
        }

        return;
    }
}
