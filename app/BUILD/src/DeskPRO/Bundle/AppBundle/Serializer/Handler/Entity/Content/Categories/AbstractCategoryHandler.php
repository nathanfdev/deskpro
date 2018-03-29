<?php

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
