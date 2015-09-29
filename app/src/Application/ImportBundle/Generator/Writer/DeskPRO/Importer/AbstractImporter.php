<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract DeskPro importer
 * Finds or creates DeskPro entities.
 *
 * Class AbstractImporter
 */
abstract class AbstractImporter extends AbstractGenerator implements ImporterInterface
{
    /**
     * @var Mapper\Collection
     */
    protected $mappers;

    /**
     * @var ArrayCollection
     */
    protected $records;

    /**
     * Constructor.
     *
     * @param Mapper\Collection $mappers
     */
    public function __construct(Mapper\Collection $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * Returns a language id by title.
     *
     * @param string $title
     *
     * @throws \Exception
     *
     * @return int
     */
    protected function findLanguageId($title)
    {
        $id = 0;
        if ($title) {
            $id = $this->getLanguageMapper()->findOneByTitle($title)->getId();
        }

        return $id;
    }

    /**
     * Returns a language by title.
     *
     * @param string $title
     *
     * @return DeskPROEntity\Language|null
     */
    protected function findLanguage($title)
    {
        $language = null;
        if ($title) {
            $language = $this->getLanguageMapper()->findOneByTitle($title);
            if ($language) {
                $this->logInfo(sprintf('Found existing `%s` language', $title));
            } else {
                $this->logNotice(sprintf('Could not map unknown `%s` language', $title));
            }
        }

        return $language;
    }

    /**
     * Returns an organization by title
     * Creates a new organization if not found.
     *
     * @param string $title
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\Organization|null
     */
    protected function findOrCreateOrganization($title)
    {
        /** @var Mapper\Organization $mapper */
        $mapper       = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION);
        $organization = null;

        if ($title) {
            $organization = $mapper->findOneByTitle($title, false);
            if ($organization) {
                $this->logDebug(sprintf(
                    'Found existing organization `%d` with title `%s`',
                    $organization->getId(), $organization->getName()
                ));
            } else {
                $organization = new DeskPROEntity\Organization();
                $organization->setName($title);

                $this->records->add($organization);
                $this->logInfo(sprintf('Creating new organization `%s`', $organization->getName()));
            }
        }

        return $organization;
    }

    /**
     * Returns the person mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Person
     */
    protected function getPersonMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON);
    }

    /**
     * Returns the language mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Language
     */
    protected function getLanguageMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_LANGUAGE);
    }

    /**
     * Returns the article mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Article
     */
    protected function getArticleMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE);
    }

    /**
     * Returns the download mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Download
     */
    protected function getDownloadMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD);
    }

    /**
     * Returns the news mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\News
     */
    protected function getNewsMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_NEWS);
    }

    /**
     * Returns the feedback mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Feedback
     */
    protected function getFeedbackMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_FEEDBACK);
    }

    /**
     * Returns the ticket mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\Ticket
     */
    protected function getTicketMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET);
    }
}
