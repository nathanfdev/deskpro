<?php

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
                $this->logger->debug("Found existing translation for {$language->getLocale()}");
            } else {
                $this->logger->debug("Creating a new translation for {$language->getLocale()}");

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
