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
