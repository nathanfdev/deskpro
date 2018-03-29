<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Domain\DomainObject;
use Application\DeskPRO\Entity\CustomFieldDefinition as FieldDefinition;
use Application\DeskPRO\TicketLayout\Layout;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\DBAL\Connection;

class CustomFieldData extends AbstractEntityRepository
{
    /**
     * @param DomainObject $owner
     * @param DomainObject $context
     * @param Layout       $layout
     *
     * @return mixed
     */
    public function getAllDataForOwner(DomainObject $owner, DomainObject $context = null, Layout $layout = null)
    {
        $qb = $this->createQueryBuilder('da')
            ->select('da, de', 'rde')
            ->innerJoin('da.definition', 'de')
            ->innerJoin('da.root_definition', 'rde')
            ->where('da.owner_id = :owner_id and de.owner_class = :owner_class and rde.is_enabled = 1')
            ->setParameters([
                'owner_id'    => (int) $owner['id'], // null -> 0
                'owner_class' => ClassUtils::getClass($owner),
            ]);

        if ($layout) {
            if (!$in = $layout->getIdsOfFieldType('custom_field')) {
                return [];
            }
            $qb
                ->andWhere('rde.id in (:fields)')
                ->setParameter('fields', $in, Connection::PARAM_INT_ARRAY);
        }

        if ($context) {
            $qb
                ->andWhere('rde.context_class is null or (de.context_class = :context_class and de.context_id = :cid)')
                ->setParameter('context_class', ClassUtils::getClass($context))
                ->setParameter('cid', $context['id']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FieldDefinition $definition
     * @param DomainObject    $owner
     *
     * @return array
     */
    public function getFieldData(FieldDefinition $definition, DomainObject $owner)
    {
        if (!$definition['id'] || !$owner['id']) {
            return [];
        }

        $qb = $this->createQueryBuilder('da')
            ->join('da.definition', 'de')
            ->where('da.owner_id = :oid and da.root_definition = :did')
            ->setParameter('oid', $owner['id'])
            ->setParameter('did', $definition['id'])
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param FieldDefinition $definition
     * @param DomainObject    $owner
     *
     * @return mixed|null
     */
    public function getFieldRawData(FieldDefinition $definition, DomainObject $owner)
    {
        if (!$definition['id'] || !$owner['id']) {
            return;
        }

        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('da.value, da.input, de.title')
            ->from($this->getEntityName(), 'da')
            ->join('da.definition', 'de')
            ->where('da.owner_id = :oid and da.root_definition = :did')
            ->setParameter('oid', $owner['id'])
            ->setParameter('did', $definition['id'])
        ;

        return $qb->getQuery()->execute();
    }
}
