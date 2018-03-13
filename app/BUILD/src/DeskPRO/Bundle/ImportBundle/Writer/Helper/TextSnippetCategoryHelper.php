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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\LanguageMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\TextSnippetCategoryMapper;
use Psr\Log\LoggerInterface;

/**
 * Class TextSnippetCategoryHelper.
 */
class TextSnippetCategoryHelper
{
    /**
     * @var TextSnippetCategoryMapper
     */
    private $categoryMapper;

    /**
     * @var LanguageMapper
     */
    private $languageMapper;

    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param TextSnippetCategoryMapper $categoryMapper
     * @param LanguageMapper            $languageMapper
     * @param ImportMapMapper           $importMapMapper
     * @param LanguageHelper            $languageHelper
     * @param EntityPersister           $persister
     * @param LoggerInterface           $logger
     */
    public function __construct(
        TextSnippetCategoryMapper $categoryMapper,
        LanguageMapper            $languageMapper,
        ImportMapMapper           $importMapMapper,
        LanguageHelper            $languageHelper,
        EntityPersister           $persister,
        LoggerInterface           $logger
    ) {
        $this->categoryMapper  = $categoryMapper;
        $this->languageMapper  = $languageMapper;
        $this->importMapMapper = $importMapMapper;
        $this->languageHelper  = $languageHelper;
        $this->persister       = $persister;
        $this->logger          = $logger;
    }

    /**
     * @param int|string $categoryOidOrName
     *
     * @return Entity\TextSnippetCategory|null
     */
    public function findOrCreateTextSnippetCategory($categoryOidOrName)
    {
        if (!$categoryOidOrName) {
            return;
        }
        if (!is_scalar($categoryOidOrName)) {
            throw new \RuntimeException('Text snippet category oid or name is not a scalar value.');
        }

        $entity = null;

        $model = new Model\TextSnippetCategory();
        $model->setOid($categoryOidOrName);

        // try to get category by oid
        $entityId = $this->importMapMapper->findIdByModel($model);
        if ($entityId) {
            $entity = $this->categoryMapper->find($entityId);
        }

        // try to get category by title
        if (!$entity) {
            $entity = $this->categoryMapper->findOneByTitle($categoryOidOrName);
        }

        if (is_int($categoryOidOrName) || ctype_digit($categoryOidOrName)) {
            $categoryName = 'Category '.$categoryOidOrName;
        } else {
            $categoryName = $categoryOidOrName;
        }

        if (!$entity) {
            $entity = $this->categoryMapper->findOneByTitle($categoryName);
        }

        // otherwise create a category by oid or name
        if (!$entity) {
            // get first installed language
            $language = $this->languageMapper->findFirst();
            if (!$language) {
                // or install english language if no one was found
                $language = $this->languageHelper->findOrCreateLanguage('eng');
            }

            $entity = new Entity\TextSnippetCategory();
            $entity->setTypename(Entity\TextSnippetCategory::TYPE_TICKET);
            $entity->setIsGlobal(true);

            $objectLang = new Entity\ObjectLang();
            $objectLang->setPropName('title');
            $objectLang->setLanguage($language);
            $objectLang->setValue($categoryName);
            $objectLang->setObject($entity);

            $entity->getObjectPropsTranslations()->add($objectLang);

            $this->persister->persistAndFlush($entity, $model);
        }

        return $entity;
    }
}
