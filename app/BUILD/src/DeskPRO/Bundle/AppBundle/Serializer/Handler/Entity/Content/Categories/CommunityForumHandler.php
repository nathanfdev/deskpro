<?php

namespace DeskPRO\Bundle\AppBundle\Serializer\Handler\Entity\Content\Categories;

use Application\DeskPRO\Entity\CommunityForum;
use Application\DeskPRO\Entity\Language;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Content\Categories\CommunityForum as CommunityForumModel;
use DeskPRO\Bundle\AppBundle\Serializer\Model\Phrase;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;

/**
 * Class CommunityForumHandler.
 */
class CommunityForumHandler extends AbstractCategoryHandler
{
    /**
     * {@inheritdoc}
     *
     * @param CommunityForum $entity
     */
    public function createModel($entity, SideloadSerializationContext $context)
    {
        $model = new CommunityForumModel($entity);
        $model->setTitleTranslations($this->getTitleTranslations($entity));
        $model->setNoonTranslations($this->getNoonTranslations($entity));
        $model->setPluralTranslations($this->getPluralTranslations($entity));

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    public static function getClassNames()
    {
        return CommunityForum::class;
    }

    /**
     * @param CommunityForum $entity
     *
     * @return array
     */
    protected function getNoonTranslations(CommunityForum $entity)
    {
        if (null === $this->languages) {
            $this->languages = $this->em->getRepository(Language::class)->findAll();
        }

        $translations = [];
        foreach ($this->languages as $language) {
            $translations[] = new Phrase($language, $this->translate->getPhraseObject($entity, 'noon', $language));
        }

        return $translations;
    }

    /**
     * @param CommunityForum $entity
     *
     * @return array
     */
    protected function getPluralTranslations(CommunityForum $entity)
    {
        if (null === $this->languages) {
            $this->languages = $this->em->getRepository(Language::class)->findAll();
        }

        $translations = [];
        foreach ($this->languages as $language) {
            $translations[] = new Phrase($language, $this->translate->getPhraseObject($entity, 'plural', $language));
        }

        return $translations;
    }
}
