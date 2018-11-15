<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Language;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Language record mapper.
 *
 * Class Language
 */
class LanguageMapper extends AbstractContainerMapper implements MapperByTitleInterface
{
    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Language::class;
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByTitle($title)
    {
        $criteria = new Criteria();
        $criteria
            ->orWhere(Criteria::expr()->eq('title', $title))
            ->orWhere(Criteria::expr()->eq('locale', $title))
            ->orWhere(Criteria::expr()->eq('sys_name', $title))
        ;

        /* @var Language[]|ArrayCollection $records */
        $records = $this->em->getRepository($this->getEntityClass())->matching($criteria);

        return $records->first() ?: null;
    }

    /**
     * @return Language|null
     */
    public function findFirst()
    {
        $result = $this->em->getRepository($this->getEntityClass())->findBy([], null, 1);

        return array_shift($result);
    }
}
