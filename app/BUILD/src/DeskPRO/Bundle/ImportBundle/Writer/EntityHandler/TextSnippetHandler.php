<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity\TextSnippet;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class TextSnippetHandler.
 */
class TextSnippetHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\TextSnippet::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\TextSnippet $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var TextSnippet $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getTextSnippetMapper(), $model);
        $entity
            ->setCategory($this->helpers->getTextSnippetCategoryHelper()->findOrCreateTextSnippetCategory($model->getCategory()))
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setShortcutCode($model->getShortcutCode())
            ->setIsDraft($model->isDraft())
        ;

        // update translations
        $this->helpers->getTranslationHelper()->updateTranslations($model->getTitleTranslations(), $entity, 'title');
        $this->helpers->getTranslationHelper()->updateTranslations($model->getSnippetTranslations(), $entity, 'snippet');

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);
    }
}
