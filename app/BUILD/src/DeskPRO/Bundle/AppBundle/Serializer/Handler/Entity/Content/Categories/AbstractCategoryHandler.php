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

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\CategoryAbstract as CategoryAbstractEntity;
use Application\DeskPRO\Entity\Language;
use Application\DeskPRO\Translate\Translate;
use DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\AbstractEntityHandler;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Phrase;
use Doctrine\ORM\EntityManager;

/**
 * Class AbstractCategoryHandler.
 */
abstract class AbstractCategoryHandler extends AbstractEntityHandler
{
    /**
     * @var Translate
     */
    protected $translate;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var Language[]
     */
    protected $languages;

    /**
     * Constructor.
     *
     * @param Translate     $translate
     * @param EntityManager $em
     */
    public function __construct(Translate $translate, EntityManager $em)
    {
        $this->translate = $translate;
        $this->em        = $em;
    }

    /**
     * @param CategoryAbstractEntity $entity
     *
     * @return array
     */
    protected function getTitleTranslations(CategoryAbstractEntity $entity)
    {
        if (null === $this->languages) {
            $this->languages = $this->em->getRepository(Language::class)->findAll();
        }

        $translations = [];
        foreach ($this->languages as $language) {
            $translations[] = new Phrase($language, $this->translate->getPhraseObject($entity, 'title', $language));
        }

        return $translations;
    }
}
