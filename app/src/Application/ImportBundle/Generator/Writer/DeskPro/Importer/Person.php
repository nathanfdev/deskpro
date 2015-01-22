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
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro person importer
 *
 * Class Person
 * @package Application\ImportBundle\Generator\Writer\DeskPro\Importer
 */
final class Person extends AbstractImporter
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_PERSON;
    }

    /**
     * {@inheritdoc}
     *
     * 'date_created' => $pval->date_created ? $pval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
     * 'is_user'      => 1,
     *  'is_contact'   => 1,
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        if (!$entity instanceof Entity\Person) {
            throw new \Exception(sprintf(
                'Entity `%s` is not supported by importer `%s`',
                get_class($entity), get_class($this)
            ));
        }

        $this->records = new ArrayCollection();

        try {
            $person = $this->mappers
                ->getMapperByType(Mapper\MapperInterface::TYPE_PERSON)
                ->findOneByValue($entity->getEmails());

        } catch (Mapper\MapperException $exception) {
            $person = new DeskPROEntity\Person();
        }

        $person
            ->setName($entity->getName())
            ->setFirstName($entity->getFirstName())
            ->setLastName($entity->getLastName())
            ->setTimezone($entity->getTimezone() ? : 'UTC')
            ->setIsAgent($entity->isAgent())
            ->setCanAdmin($entity->isAdmin());

        if ($entity->getPassword() && $entity->getPasswordScheme() == Entity\Person::PASSWORD_SCHEME_PLAIN) {
            $person->setPassword($entity->getPassword());
        }

        $this->setLanguage($entity, $person);
        $this->setOrganization($entity, $person);

        $this->records->add($person);

        return $this->records;
    }

    /**
     * Set language
     *
     * @param Entity\Person        $entity
     * @param DeskPROEntity\Person $person
     */
    private function setLanguage(Entity\Person $entity, DeskPROEntity\Person $person)
    {
        if (!$entity->getLanguage()) {
            return;
        }

        /** @var DeskPROEntity\Language $language */
        $language = $this->mappers
            ->getMapperByType(Mapper\MapperInterface::TYPE_LANGUAGE)
            ->findOneByValue($entity->getLanguage());

        $person->setLanguageId($language->getId());
    }

    /**
     * Set organization
     * If the organization not found create a new one by name
     *
     * @param Entity\Person        $entity
     * @param DeskPROEntity\Person $person
     */
    private function setOrganization(Entity\Person $entity, DeskPROEntity\Person $person)
    {
        if (!$entity->getOrganization()) {
            return;
        }
        try {
            $organization = $this->mappers
                ->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION)
                ->findOneByValue($entity->getOrganization());

        } catch (Mapper\MapperException $exception) {
            $organization = new DeskPROEntity\Organization();
            $this->records->add($person);
        }

        $person->setOrganization($organization);
    }
}
