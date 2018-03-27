<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\ObjectLang;
use Application\DeskPRO\Entity\TextSnippetCategory;
use Doctrine\ORM\Query\Expr\Join;

/**
 * Class TextSnippetCategoryMapper.
 */
class TextSnippetCategoryMapper extends AbstractContainerMapper implements MapperByTitleInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return TextSnippetCategory::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByTitle($title)
    {
        $qb = $this->em->createQueryBuilder();
        $qb
            ->select('c')
            ->from($this->getEntityClass(), 'c')
            ->join(ObjectLang::class, 'o', Join::WITH, 'o.ref_id = c.id')
            ->andWhere(
                'o.prop_name = :prop_name',
                'o.ref_type = :ref_type',
                'o.value = :title'
            )
            ->setParameter('prop_name', 'title')
            ->setParameter('ref_type', 'text_snippet_categories')
            ->setParameter('title', $title)
        ;

        $result = $qb->getQuery()->getResult();

        return array_shift($result);
    }
}
