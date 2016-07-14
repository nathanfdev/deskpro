<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Model\ObjectLang;
use Application\ImportBundle\Writer\Mapper\MapperRegistry;
use Application\ImportBundle\Writer\Mapper\OrganizationMapper;
use Psr\Log\LoggerInterface;

/**
 * Abstract DeskPRO importer
 * Finds or creates DeskPRO entities.
 *
 * Class AbstractImporter
 */
abstract class AbstractEntityHandler implements EntityHandlerInterface
{
    /**
     * @var MapperRegistry
     */
    protected $mappers;

    /**
     * @var DoctrineEntities
     */
    protected $records;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor.
     *
     * @param MapperRegistry  $mappers
     * @param LoggerInterface $logger
     */
    public function __construct(MapperRegistry $mappers, LoggerInterface $logger)
    {
        $this->mappers = $mappers;
        $this->logger  = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->records = new DoctrineEntities();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDoctrineEntities()
    {
        return $this->records;
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
            $id = $this->mappers->getLanguageMapper()->findOneByTitle($title)->getId();
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
            $language = $this->mappers->getLanguageMapper()->findOneByTitle($title);
            if ($language) {
                $this->logger->info(sprintf('Found existing `%s` language', $title));
            } else {
                $this->logger->notice(sprintf('Could not map unknown `%s` language', $title));
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
        /** @var OrganizationMapper $mapper */
        $mapper       = $this->mappers->getMapper(DeskPROEntity\Organization::class);
        $organization = null;

        if ($title) {
            $organization = $mapper->findOneByTitle($title, false);
            if ($organization) {
                $this->logger->debug(sprintf(
                    'Found existing organization `%d` with title `%s`',
                    $organization->getId(), $organization->getName()
                ));
            } else {
                $organization = new DeskPROEntity\Organization();
                $organization->setName($title);

                $this->records->addRelatedEntity($organization);
                $this->logger->info(sprintf('Creating new organization `%s`', $organization->getName()));
            }
        }

        return $organization;
    }

    /**
     * Returns an user group by sys name.
     *
     * @param string $sys_name
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\UserGroup|null
     */
    protected function findUserGroup($sys_name)
    {
        $mapper     = $this->mappers->getUserGroupMapper();
        $user_group = null;

        if ($sys_name) {
            $user_group = $mapper->findOneBySysName($sys_name, false);
            if ($user_group) {
                $this->logger->debug(sprintf(
                    'Found existing user group `%d` with title `%s`',
                    $user_group->getId(), $user_group->getTitle()
                ));
            } else {
                $this->logger->warning(sprintf('No user group `%s`', $sys_name));
            }
        }

        return $user_group;
    }

    /**
     * Creates object lang.
     *
     * @param ObjectLang $translation
     * @param mixed      $entity
     */
    protected function addObjectLang(ObjectLang $translation, $entity)
    {
        $language = $this->mappers->getLanguageMapper()->findOneByTitle($translation->getLanguage(), false);
        if (!$language) {
            return;
        }

        $objectLang = DeskPROEntity\ObjectLang::createObjectLang($language, $entity, $translation->getProperty(), $translation->getValue());

        $this->logger->debug("Add new {$translation->getProperty()} translation with {$translation->getValue()}");
        $this->records->addRelatedEntity($objectLang);
    }
}
