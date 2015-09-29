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
use Application\ImportBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * DeskPro person importer.
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
     *
     * @var Entity\Person
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        if ($entity->isAgent()) {
            $this->logAlert(sprintf('Importing agent `%s`', $entity->getFirstEmail()));
        }

        $person = $this->findOrCreatePerson($entity->getEmails());
        $person
            ->setName($entity->getName())
            ->setFirstName($entity->getFirstName())
            ->setLastName($entity->getLastName())
            ->setTimezone($entity->getTimezone())
            ->setIsAgent($entity->isAgent())
            ->setCanAgent($entity->isAgent())
            ->setCanAdmin($entity->isAdmin())
            ->setDateCreated($entity->getDateCreated())
            ->setLanguageId($this->findLanguageId($entity->getLanguage()))
            ->setOrganization($this->findOrCreateOrganization($entity->getOrganization()))
            ->setOrganizationPosition($entity->getOrganizationPosition())
            ->resetEmails()
            ->resetLabels()
            ->resetUsergroups()
        ;

        if ($entity->isAgent() && !in_array('agent_all_safe_perms', $entity->getUserGroups(), true)) {
            $entity->addUserGroup('agent_all_safe_perms');
        }

        if ($entity->getPassword()) {
            if ($entity->isPlainPasswordScheme()) {
                $person->setPassword($entity->getPassword());
            } else {
                $this->logAlert(sprintf('Password scheme `%s` is not supported', $entity->getPasswordScheme()));
            }
        } else {
            if ($entity->isUser()) {
                $person->setPassword(Entity\Person::INITIAL_PASSWORD);
            }
        }

        foreach ($entity->getEmails() as $num => $email) {
            if ($this->getEmailAccountMapper()->findOneByEmail($email, false)) {
                $this->logWarning(sprintf('Email `%s` is an a gateway account address (Skipping)', $email));
            } else {
                $person->addEmailAddress($this->findOrCreatePersonEmail($email));
                $this->logDebug(sprintf(
                    $num ? 'Set email `%s`' : 'Set primary email `%s`',
                    $entity->getFirstEmail()
                ));
            }
        }
        foreach ($entity->getUserGroups() as $user_group_name) {
            $user_group = $this->findUserGroup($user_group_name);
            if ($user_group) {
                $person->addUsergroup($user_group);
            }
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $person->addCustomData($this->createCustomData($custom_field));
        }

        $this->records->add($person);

        return $this->records;
    }

    /**
     * Returns a person entity
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
     * Returns a person email entity.
     *
     * @param string $email_string
     *
     * @return DeskPROEntity\PersonEmail
     */
    private function findOrCreatePersonEmail($email_string)
    {
        $email = $this->getPersonEmailMapper()->findOneByEmail($email_string, false);
        if ($email) {
            $this->logDebug(sprintf(
                'Found existing person email, id=`%d` with email `%s`',
                $email->getId(), $email->getEmail()
            ));
        } else {
            $email = new DeskPROEntity\PersonEmail();
            $email
                ->setEmail($email_string)
                ->setIsValidated(true)
            ;

            $this->records->add($email);
            $this->logInfo(sprintf('Creating new person email `%s`', $email->getEmail()));
        }

        return $email;
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
    private function findUserGroup($sys_name)
    {
        /** @var Mapper\UserGroup $mapper */
        $mapper     = $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_USER_GROUP);
        $user_group = null;

        if ($sys_name) {
            $user_group = $mapper->findOneBySysName($sys_name, false);
            if ($user_group) {
                $this->logDebug(sprintf(
                    'Found existing user group `%d` with title `%s`',
                    $user_group->getId(), $user_group->getTitle()
                ));
            } else {
                $this->logWarning(sprintf('No user group `%s`', $sys_name));
            }
        }

        return $user_group;
    }

    /**
     * Returns custom def person entity.
     *
     * @param Entity\CustomField $entity
     *
     * @throws ImporterException
     *
     * @return DeskPROEntity\CustomDataPerson
     */
    private function createCustomData(Entity\CustomField $entity)
    {
        $person_def   = $this->getCustomDefPersonMapper()->findOneByTitle($entity->getKey());
        $custom_field = new DeskPROEntity\CustomDataPerson();

        switch ($person_def->getTypeName()) {
            case Entity\CustomField::FIELD_TYPE_TEXT:
            case Entity\CustomField::FIELD_TYPE_TEXTAREA:
                $custom_field
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($entity->getValue());

                break;

            case Entity\CustomField::FIELD_TYPE_TOGGLE:
                $custom_field
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($entity->getValue() ? 1 : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_DATE:
                $custom_field
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($entity->getValue() ? strtotime($entity->getValue()) : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_CHOICE:
                $choice_def = $this->getCustomDefPersonMapper()->findOneByTitle($entity->getValue());
                $custom_field
                    ->setField($choice_def)
                    ->setRootField($person_def)
                    ->setValue(1);

                break;

            default:
                throw new ImporterException('Unknown custom field type `%s`', $person_def->getTypeName());
        }

        $this->records->add($custom_field);

        return $custom_field;
    }

    /**
     * Returns the person email mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\PersonEmail
     */
    private function getPersonEmailMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON_EMAIL);
    }

    /**
     * Returns the email account mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\EmailAccount
     */
    private function getEmailAccountMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_EMAIL_ACCOUNT);
    }

    /**
     * Returns the custom def person mapper.
     *
     * @throws \Exception
     *
     * @return Mapper\CustomDefPerson
     */
    private function getCustomDefPersonMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_CUSTOM_DEF_PERSON);
    }
}
