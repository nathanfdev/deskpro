<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Generator\Writer\DeskPro\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract DeskPro importer
 * Finds or creates DeskPro entities
 *
 * Class AbstractImporter
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
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
     * Constructor
     *
     * @param Mapper\Collection $mappers
     */
    public function __construct(Mapper\Collection $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * Returns a language id by title
     *
     * @param string $title
     *
     * @return int
     * @throws \Exception
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
     * Returns a language by title
     *
     * @param string $title
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
     * Creates a new organization if not found
     *
     * @param string $title
     *
     * @return DeskPROEntity\Organization|null
     * @throws \Exception
     */
    protected function findOrCreateOrganization($title)
    {
        /** @var Mapper\Organization $mapper */
        $mapper       = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION);
        $organization = null;

        if ($title) {
            $organization = $mapper->findOneByTitle($title, false);
            if ($organization) {
                $this->logInfo(sprintf(
                    'Found existing organization `%d` with title `%s`',
                    $organization->getId(), $organization->getName()
                ));
            } else {
                $entity = new DeskPROEntity\Organization();
                $entity->setName($title);

                $this->records->add($organization);
                $this->logWarning(sprintf('Creating new organization `%s`', $entity->getName()));
            }
        }

        return $organization;
    }

    /**
     * Returns the person mapper
     *
     * @return Mapper\Person
     * @throws \Exception
     */
    protected function getPersonMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON);
    }

    /**
     * Returns the language mapper
     *
     * @return Mapper\Language
     * @throws \Exception
     */
    protected function getLanguageMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_LANGUAGE);
    }

    /**
     * Returns the article mapper
     *
     * @return Mapper\Article
     * @throws \Exception
     */
    protected function getArticleMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ARTICLE);
    }
}
