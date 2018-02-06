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
            if ($info['locale'] === $title || $info['id'] === $title || $info['title'] === $title || $info['lang_code'] === $title) {
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
