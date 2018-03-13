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

use Application\DeskPRO\Entity\ObjectLang;
use DeskPRO\Bundle\AppBundle\Entity\ObjectTranslatableInterface;
use DeskPRO\Bundle\ImportBundle\Model\Translation;
use Psr\Log\LoggerInterface;

/**
 * Class TranslationHelper.
 */
class TranslationHelper
{
    /**
     * @var LanguageHelper
     */
    private $languageHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param LanguageHelper  $languageHelper
     * @param LoggerInterface $logger
     */
    public function __construct(LanguageHelper $languageHelper, LoggerInterface $logger)
    {
        $this->languageHelper = $languageHelper;
        $this->logger         = $logger;
    }

    /**
     * @param Translation[]               $modelTranslations
     * @param ObjectTranslatableInterface $entity
     * @param string                      $propertyName
     */
    public function updateTranslations(array $modelTranslations, ObjectTranslatableInterface $entity, $propertyName)
    {
        $entityTranslations = $entity->getObjectPropsTranslations();

        // add or update translations
        foreach ($modelTranslations as $modelTranslation) {
            $language = $this->languageHelper->findOrCreateLanguage($modelTranslation->getLanguage());
            if (!$language) {
                $this->logger->warning("Unable to get language entity for `{$modelTranslation->getLanguage()}`, skipping");
                continue;
            }

            /** @var ObjectLang $objectLang */
            $objectLang = $entityTranslations
                ->filter(function (ObjectLang $objectLang) use ($language, $propertyName) {
                    return $objectLang->getLanguage() === $language && $objectLang->getPropName() === $propertyName;
                })
                ->first()
            ;

            if ($objectLang) {
                $this->logger->debug("Found existing translation for {$language->getLangCode()}");
            } else {
                $this->logger->debug("Creating a new translation for {$language->getLangCode()}");

                $objectLang = new ObjectLang();
                $objectLang->setPropName($propertyName);
                $objectLang->setLanguage($language);
                $objectLang->setObject($entity);

                $entityTranslations->add($objectLang);
            }

            $objectLang->setValue($modelTranslation->getValue());
        }
    }
}
