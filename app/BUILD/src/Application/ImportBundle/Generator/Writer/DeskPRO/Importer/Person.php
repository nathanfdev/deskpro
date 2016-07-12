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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer;

use Application\DeskPRO\Entity as DeskPROEntity;
use Application\ImportBundle\Entity;

/**
 * DeskPRO person importer.
 *
 * Class Person
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
     */
    public function prepare(Entity\EntityInterface $entity, $entity_id = null)
    {
        if (!$entity instanceof Entity\Person) {
            Entity\UnexpectedException::throwUnexpectedEntityTypeException($entity);
        }

        if ($entity->isAgent()) {
            $this->logAlert(sprintf('Importing agent `%s`', $entity->getFirstEmail()));
        }

        $person = $this->findOrCreatePerson($entity->getEmails());
        $person
            ->setFirstName($entity->getFirstName())
            ->setLastName($entity->getLastName())
            ->setName($entity->getName())
            ->setTimezone($entity->getTimezone())
            ->setIsAgent($entity->isAgent())
            ->setCanAgent($entity->isAgent())
            ->setCanAdmin($entity->isAdmin())
            ->setLanguage($entity->getLanguage() ? $this->findLanguage($entity->getLanguage()) : null)
            ->setIsDisabled($entity->isDisabled())
            ->setIsDeleted($entity->isDeleted())
            ->setDateCreated($entity->getDateCreated())
            ->setOrganization($this->findOrCreateOrganization($entity->getOrganization()))
            ->setOrganizationPosition($entity->getOrganizationPosition())
            ->resetEmails()
            ->resetLabels()
            ->resetUsergroups()
            ->resetContactData()
            ->resetCustomData()
        ;

        if ($entity->isAgent() && !in_array('agent_all_safe_perms', $entity->getUserGroups(), true)) {
            $entity->addUserGroup('agent_all_safe_perms');
        }

        if ($entity->getPassword()) {
            if ($entity->isPlainPasswordScheme()) {
                $person->setPassword($entity->getPassword());
            } else {
                $this->logAlert(sprintf('Password scheme `%s` is not supported. Set initial password.', $entity->getPasswordScheme()));
                $person->setPassword(Entity\Person::INITIAL_PASSWORD);
            }
        } else {
            if ($entity->isUser()) {
                $person->setPassword(Entity\Person::INITIAL_PASSWORD);
            }
        }

        foreach ($entity->getEmails() as $email) {
            if ($this->getEmailAccountMapper()->findOneByEmail($email, false)) {
                $this->logWarning(sprintf('Email `%s` is an a gateway account address (Skipping)', $email));
            } else {
                if (!count($person->getEmailAddresses())) {
                    $person->setEmail($email);
                    $this->logDebug(sprintf('Set primary email `%s`', $entity->getFirstEmail()));
                } else {
                    $person->addEmailAddressString($email);
                    $this->logDebug(sprintf('Set email `%s`', $entity->getFirstEmail()));
                }
            }
        }
        foreach ($entity->getUserGroups() as $user_group_name) {
            $user_group = $this->findUserGroup($user_group_name);
            if ($user_group) {
                $person->addUsergroup($user_group);
            }
        }
        foreach ($entity->getContactData() as $contact) {
            $person->addContactData($this->createContactData($contact));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $custom_field = $this->createPersonCustomData($custom_field);
            if ($custom_field) {
                $person->addCustomData($custom_field);
            }
        }

        $this->records->setPrimaryEntity($person);
    }

    /**
     * Returns a person entity.
     * Creates a new person if not found.
     *
     * @param array $emails
     *
     * @throws \Exception
     *
     * @return DeskPROEntity\Person
     */
    private function findOrCreatePerson(array $emails)
    {
        $emails = array_values($emails);
        if (empty($emails)) {
            throw new ImporterException('Unable to find or create without primary email');
        }

        $person = $this->getPersonMapper()->findOneByEmails($emails, false);
        if ($person) {
            $this->logDebug(sprintf(
                'Found existing user, id=`%d` with email `%s`',
                $person->getId(), $person->getEmailAddress()
            ));
        } else {
            $person = new DeskPROEntity\Person();
            $this->logInfo(sprintf('Creating new person with email `%s`', $emails[0]));
        }

        return $person;
    }

    /**
     * Returns person contact data entity.
     *
     * @param Entity\ContactData $entity
     *
     * @return DeskPROEntity\PersonContactData
     */
    private function createContactData(Entity\ContactData $entity)
    {
        $contact = new DeskPROEntity\PersonContactData();
        $contact
            ->setContactType($entity->getContactType())
            ->setComment($entity->getComment())
            ->setField1($entity->getField1())
            ->setField2($entity->getField2())
            ->setField3($entity->getField3())
            ->setField4($entity->getField4())
            ->setField5($entity->getField5())
            ->setField6($entity->getField6())
            ->setField7($entity->getField7())
            ->setField8($entity->getField8())
            ->setField9($entity->getField9())
            ->setField10($entity->getField10())
        ;

        return $contact;
    }

    /**
     * Returns person custom data entity.
     *
     * @param Entity\CustomField $entity
     * 
     * @return DeskPROEntity\CustomDataPerson
     */
    private function createPersonCustomData(Entity\CustomField $entity)
    {
        return $this->createCustomData($this->getPersonCustomDefMapper(), $entity, new DeskPROEntity\CustomDataPerson());
    }

    /**
     * Returns the email account mapper.
     *
     * @return Mapper\EmailAccount
     */
    private function getEmailAccountMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_EMAIL_ACCOUNT);
    }
}
