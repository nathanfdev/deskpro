<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;

class CustomFieldDefinition extends AbstractEntityRepository
{
    public function getAllDefinitionsForOwner(DomainObject $object, DomainObject $context = null, Layout $layout = null)
    {
        $qb = $this->createQueryBuilder('d')
            ->where('d.parent is null')
            ->andWhere('d.owner_class = :owner')
            ->andWhere('d.is_enabled = 1')
            ->setParameter('owner', ClassUtils::getClass($object));

        if ($layout) {
            if (!$in = $layout->getIdsOfFieldType('custom_field')) {
                return [];
            }
            $qb
                ->andWhere('d.id in (:fields)')
                ->setParameter('fields', $in, Connection::PARAM_INT_ARRAY);
        }

        if ($context) {
            if ($context['id']) {
                // not contextual fields
                // or contextual fields without children (single input)
                // or contextual fields with children (choices)
                $qb
                    ->leftJoin('d.children', 'dc')
                    ->andWhere('
                        d.context_class is null or d.context_class = :context_class
                        or (d.context_class = :context_class and d.context_id = :cid)
                        or (dc.context_class = :context_class and dc.context_id = :cid)
                    ')
                    ->setParameter('cid', (int) $context['id'])
                    ->setParameter('context_class', ClassUtils::getClass($context));
            } else {
                $qb
                    ->andWhere('d.context_class is null or d.context_class = :context_class')
                    ->setParameter('context_class', ClassUtils::getClass($context));
            }
        } else {
            $qb->andWhere('d.context_class is null');
        }

        return $qb->getQuery()->getResult();
    }
}
