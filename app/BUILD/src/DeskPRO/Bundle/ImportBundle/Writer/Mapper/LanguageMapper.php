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
            ->orWhere(Criteria::expr()->eq('lang_code', $title))
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
