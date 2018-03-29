<?php

namespace DeskPRO\Bundle\AppBundle\Entity\Repository;

use Application\DeskPRO\Entity\Blob;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

/**
 * Class SnippetTranslationRepository.
 */
class SnippetTranslationRepository extends AbstractEntityRepository
{
    /**
     * @param Blob $blob
     *
     * @return Blob|null
     */
    public function findSnippetBlob(Blob $blob)
    {
        $qb = $this->createQueryBuilder('st');

        $qb
            ->select('st')
            ->innerJoin('st.blobs', 'b')
            ->where('b.id = :blob')
            ->setParameter('blob', $blob)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }
}
