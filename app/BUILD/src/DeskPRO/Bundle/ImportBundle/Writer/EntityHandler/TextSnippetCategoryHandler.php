<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity\TextSnippetCategory;
use DeskPRO\Bundle\ImportBundle\Model;

/**
 * Class TextSnippetCategoryHandler.
 */
class TextSnippetCategoryHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\TextSnippetCategory::class;
    }

    /**
     * {@inheritdoc}
     *
     * @param Model\TextSnippetCategory $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        /** @var TextSnippetCategory $entity */
        $entity = $this->findOrCreateEntity($this->mappers->getTextSnippetCategoryMapper(), $model);
        $entity
            ->setPerson($this->helpers->getPersonHelper()->findOrCreatePerson($model->getPerson()))
            ->setTypename($model->getTypename())
            ->setIsGlobal($model->isGlobal())
        ;

        // update translations
        $this->helpers->getTranslationHelper()->updateTranslations($model->getTitleTranslations(), $entity, 'title');

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);
    }
}
