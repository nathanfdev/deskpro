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
     * todo can we get exist person by first email only? Maybe we should check all?
     * todo should we skip not existing languages?
     * todo reset deskpro entities property update?
     *
     * 'date_created' => $pval->date_created ? $pval->date_created->format('Y-m-d H:i:s') : date('Y-m-d H:i:s'),
     * 'is_user'      => 1,
     * 'is_contact'   => 1,
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

        /** @var Mapper\Person $person_mapper */
        $person_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON);
        /** @var Mapper\Organization $organization_mapper */
        $organization_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_ORGANIZATION);
        /** @var Mapper\UserGroup $user_group_mapper */
        $user_group_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_USER_GROUP);

        $person = $person_mapper->findOneByEmail($entity->getFirstEmail(), false) ? : new DeskPROEntity\Person();
        if ($person->getId()) {
            $this->logInfo(sprintf(
                'Found existing user `%d` with email `%s`',
                $person->getId(), $entity->getFirstEmail()
            ));
        } else {
            $this->logInfo(sprintf(
                'Creating new person with email `%s`',
                $entity->getFirstEmail()
            ));
        }

        $person
            ->setName($entity->getName())
            ->setFirstName($entity->getFirstName())
            ->setLastName($entity->getLastName())
            ->setTimezone($entity->getTimezone())
            ->setIsAgent($entity->isAgent())
            ->setCanAdmin($entity->isAdmin())
            ->resetEmails()
            ->resetLabels()
            ->resetUsergroups();

        foreach ($entity->getEmails() as $num => $email_string) {
            $email = $this->createPersonEmail($email_string);
            $person->addEmailAddress($email);
            $this->records->add($email);

            $this->logInfo(sprintf(
                $num ? 'Set email `%s`' : 'Set primary email `%s`',
                $entity->getFirstEmail()
            ));
        }
        foreach ($entity->getLabels() as $label_name) {
            $label = $this->createPersonLabel($label_name);
            $person->addLabel($label);
            $this->records->add($label);
        }
        foreach ($entity->getUserGroups() as $user_group_name) {
            $user_group = $user_group_mapper->findOneByTitle($user_group_name);
            $person->addUsergroup($user_group);
        }

        if ($entity->getPassword() && $entity->isPlainPasswordScheme()) {
            $person->setPassword($entity->getPassword());
        }
        if ($entity->getLanguage()) {
            /** @var Mapper\Language $language_mapper */
            $language_mapper = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_LANGUAGE);
            $person->setLanguageId($language_mapper->findOneByTitle($entity->getLanguage())->getId());
        }
        if ($entity->getOrganization()) {
            $organization = $organization_mapper->findOneByTitle($entity->getOrganization(), false);
            if ($organization) {
                $this->logInfo(sprintf(
                    'Found existing organization `%d` with title `%s`',
                    $organization->getId(), $entity->getOrganization()
                ));
            } else {
                $organization = $this->createOrganization($entity->getOrganization());
                $this->records->add($organization);

                $this->logInfo(sprintf(
                    'Creating new organization `%s`',
                    $entity->getOrganization()
                ));
            }
            if ($entity->getOrganizationPosition()) {
                $person->setOrganizationPosition($entity->getOrganizationPosition());
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
        return $entity;
    }
}
