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
     * todo reset deskpro entities property update (reset methods - resetEmails, resetLabels, resetUsergroups)?
     * todo is_user is false by default and it = true in the setPassword method, can we set it = true without a password
     */
    public function getDoctrineEntities(Entity\EntityInterface $importing_entity)
    {
        if (!$importing_entity instanceof Entity\Person) {
            throw new \Exception(sprintf(
                'Entity `%s` is not supported by importer `%s`',
                get_class($importing_entity), get_class($this)
            ));
        }

        $this->records = new ArrayCollection();

        /** @var Mapper\Person $person_mapper */
        $person_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON);
        /** @var Mapper\Organization $organization_mapper */
        $organization_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION);
        /** @var Mapper\UserGroup $user_group_mapper */
        $user_group_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_USER_GROUP);

        $person = $person_mapper->findOneByEmails($importing_entity->getEmails(), false) ? : new DeskPROEntity\Person();
        if ($person->getId()) {
            $this->logInfo(sprintf(
                'Found existing user `%d` with email `%s`',
                $person->getId(), $person->getEmailAddress()
            ));
        } else {
            $this->logInfo(sprintf(
                'Creating new person with email `%s`',
                $importing_entity->getFirstEmail()
            ));
        }

        $person
            ->setName($importing_entity->getName())
            ->setFirstName($importing_entity->getFirstName())
            ->setLastName($importing_entity->getLastName())
            ->setTimezone($importing_entity->getTimezone())
            ->setIsAgent($importing_entity->isAgent())
            ->setCanAdmin($importing_entity->isAdmin())
            ->setDateCreated($importing_entity->getDateCreated())
            ->setLanguageId($this->getLanguageId($importing_entity->getLanguage()))
            ->resetEmails()
            ->resetLabels()
            ->resetUsergroups();

        foreach ($importing_entity->getEmails() as $num => $email_string) {
            $email = $this->createPersonEmail($email_string);
            $person->addEmailAddress($email);

            $this->logInfo(sprintf(
                $num ? 'Set email `%s`' : 'Set primary email `%s`',
                $importing_entity->getFirstEmail()
            ));
        }
        foreach ($importing_entity->getLabels() as $label_name) {
            $label = $this->createPersonLabel($label_name);
            $person->addLabel($label);
        }
        foreach ($importing_entity->getUserGroups() as $user_group_name) {
            $user_group = $user_group_mapper->findOneByTitle($user_group_name);
            $person->addUsergroup($user_group);
        }

        if ($importing_entity->getPassword() && $importing_entity->isPlainPasswordScheme()) {
            $person->setPassword($importing_entity->getPassword());
        }
        if ($importing_entity->getOrganization()) {
            $organization = $organization_mapper->findOneByTitle($importing_entity->getOrganization(), false);
            if ($organization) {
                $this->logInfo(sprintf(
                    'Found existing organization `%d` with title `%s`',
                    $organization->getId(), $importing_entity->getOrganization()
                ));
            } else {
                $organization = $this->createOrganization($importing_entity->getOrganization());
                $this->logInfo(sprintf(
                    'Creating new organization `%s`',
                    $importing_entity->getOrganization()
                ));
            }
            if ($importing_entity->getOrganizationPosition()) {
                $person->setOrganizationPosition($importing_entity->getOrganizationPosition());
            }

            $person->setOrganization($organization);
        }

        $this->records->add($person);
        return $this->records;
    }

    /**
     * Returns a person email entity
     *
     * @param string $email
     * @return DeskPROEntity\PersonEmail
     */
    private function createPersonEmail($email)
    {
        $entity = new DeskPROEntity\PersonEmail();
        $entity
            ->setEmail($email)
            ->setIsValidated(true);

        $this->records->add($email);
        return $entity;
    }

    /**
     * Returns a new person label entity
     *
     * @param string $label
     * @return DeskPROEntity\LabelPerson
     */
    private function createPersonLabel($label)
    {
        $entity = new DeskPROEntity\LabelPerson();
        $entity->setLabel($label);

        $this->records->add($label);
        return $entity;
    }

    /**
     * Returns a new organization
     *
     * @param string $organization
     * @return DeskPROEntity\Organization
     */
    private function createOrganization($organization)
    {
        $entity = new DeskPROEntity\Organization();
        $entity->setName($organization);

        $this->records->add($organization);
        return $entity;
    }
}
