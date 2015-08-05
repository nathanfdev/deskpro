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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Generator\AbstractGenerator;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper\MapperInterface;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Abstract DeskPRO importer
 * Finds or creates DeskPRO entities
 *
 * Class AbstractImporter
 * @package Application\ImportBundle\Generator\Writer\DeskPRO\Importer
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
        if (!$title) return null;
        /** @var Mapper\Organization $mapper */
        $mapper       = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION);

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

        return $organization;
    }

    /**
     * Returns custom def person entity
     *
     * @param MapperInterface                  $mapper
     * @param Entity\CustomField               $entity
     * @param DeskPROEntity\CustomDataAbstract $custom_field
     *
     * @return DeskPROEntity\CustomDataTicket
     * @throws ImporterException
     */
    protected function createCustomData(MapperInterface $mapper, Entity\CustomField $entity, DeskPROEntity\CustomDataAbstract $custom_field)
    {
        $custom_field_def = $mapper->findOneBy(array(
            'title'  => $entity->getKey(),
            'parent' => null,
        ));

        switch ($custom_field_def->getTypeName()) {
            case Entity\CustomField::FIELD_TYPE_TEXT:
            case Entity\CustomField::FIELD_TYPE_TEXTAREA:
                $custom_field
                    ->setField($custom_field_def)
                    ->setRootField($custom_field_def)
                    ->setInput($entity->getValue())
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_TOGGLE:
                $custom_field
                    ->setField($custom_field_def)
                    ->setRootField($custom_field_def)
                    ->setValue($entity->getValue() ? 1 : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_DATE:
            case Entity\CustomField::FIELD_TYPE_DATETIME:
                $custom_field
                    ->setField($custom_field_def)
                    ->setRootField($custom_field_def)
                    ->setValue($entity->getValue() ? strtotime($entity->getValue()) : 0)
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_CHOICE:
                /** @var DeskPROEntity\CustomDataAbstract $choice */
                $choice = $this->findChoiceCustomDef($mapper, $entity->getValue(), $custom_field_def);
                $custom_field
                    ->setField($choice)
                    ->setRootField($custom_field_def)
                    ->setValue(1)
                ;

                break;

            case Entity\CustomField::FIELD_TYPE_DISPLAY:
            case Entity\CustomField::FIELD_TYPE_HIDDEN:
                return null;

            default:
                throw new ImporterException('Unknown custom field type `%s`', $custom_field_def->getTypeName());
        }

        $this->records->add($custom_field);
        return $custom_field;
    }

    /**
     * Find a choice custom def entity
     * We store value for choice custom fields like "A > A1"
     *
     * MyField
     *   Option A
     *     |- Option A1
     *     |- Option A2
     *   Option B
     *     |- Option B1
     *     |- Option B2
     *
     * @param MapperInterface                 $mapper
     * @param array|string                    $choice_chain
     * @param DeskPROEntity\CustomDefAbstract $parent
     *
     * @return DeskPROEntity\CustomDefAbstract
     */
    protected function findChoiceCustomDef(MapperInterface $mapper, $choice_chain, DeskPROEntity\CustomDefAbstract $parent)
    {
        if (is_string($choice_chain)) {
            $choice_chain = explode('>', $choice_chain);
            $choice_chain = array_map('trim', $choice_chain);
        }

        $custom_field_def = $mapper->findOneBy(array(
            'title'  => array_shift($choice_chain),
            'parent' => $parent,
        ));

        return empty($choice_chain) ? $custom_field_def : $this->findChoiceCustomDef($mapper, $choice_chain, $custom_field_def);
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

    /**
     * Returns the download mapper
     *
     * @return Mapper\Download
     * @throws \Exception
     */
    protected function getDownloadMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_DOWNLOAD);
    }

    /**
     * Returns the news mapper
     *
     * @return Mapper\News
     * @throws \Exception
     */
    protected function getNewsMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_NEWS);
    }

    /**
     * Returns the feedback mapper
     *
     * @return Mapper\Feedback
     * @throws \Exception
     */
    protected function getFeedbackMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_FEEDBACK);
    }

    /**
     * Returns the ticket mapper
     *
     * @return Mapper\Ticket
     * @throws \Exception
     */
    protected function getTicketMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_TICKET);
    }
}
