<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity;
use Application\DeskPRO\Languages\LangPackInfo;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\LanguageMapper;
use Psr\Log\LoggerInterface;

/**
 * Class LanguageHelper.
 */
class LanguageHelper
{
    /**
     * @var LanguageMapper
     */
    private $mapper;

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
     * @param LanguageMapper  $mapper
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(LanguageMapper $mapper, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->mapper    = $mapper;
        $this->persister = $persister;
        $this->logger    = $logger;
    }

    /**
     * @param $title
     *
     * @return Entity\Language|null
     */
    public function findOrCreateLanguage($title)
    {
        if (!$title) {
            return;
        }

        // try to find existing language
        $language = $this->mapper->findOneByTitle($title);
        if ($language) {
            return $language;
        }

        // try to install a new language from the language pack
        $langPacks = new LangPackInfo();
        foreach ($langPacks->getManifest() as $info) {
            if ($info['locale'] === $title || $info['id'] === $title || $info['title'] === $title) {
                $language = $langPacks->newLanguageEntity($info['id']);
                $this->persister->persistAndFlush($language);

                break;
            }
        }

        if (!$language) {
            $this->logger->warning("Language `$title` is not supported.");
        }

        return $language;
    }
}
