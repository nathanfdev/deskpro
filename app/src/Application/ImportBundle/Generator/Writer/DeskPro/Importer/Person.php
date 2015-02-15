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
     * @var Entity\Person $entity
     *
     * todo is_user is false by default and it = true in the setPassword method, can we set it = true without a password
     */
    public function getDoctrineEntities(Entity\EntityInterface $entity)
    {
        $this->records = new ArrayCollection();

        if ($entity->isAgent()) {
            $this->logWarning(sprintf('Importing agent `%s`', $entity->getFirstEmail()));
        }

        $person = $this->findOrCreatePerson($entity->getEmails());
        $person
            ->setName($entity->getName())
            ->setFirstName($entity->getFirstName())
            ->setLastName($entity->getLastName())
            ->setTimezone($entity->getTimezone())
            ->setIsAgent($entity->isAgent())
            ->setCanAdmin($entity->isAdmin())
            ->setDateCreated($entity->getDateCreated())
            ->setLanguageId($this->findLanguageId($entity->getLanguage()))
            ->setOrganization($this->findOrCreateOrganization($entity->getOrganization()))
            ->setOrganizationPosition($entity->getOrganizationPosition())
            ->resetEmails()
            ->resetLabels()
            ->resetUsergroups();

        if ($entity->getPassword() && $entity->isPlainPasswordScheme()) {
            $person->setPassword($entity->getPassword());
        }
        foreach ($entity->getEmails() as $num => $email) {
            if ($this->getEmailAccountMapper()->findOneByEmail($email, false)) {
                $this->logError(sprintf('Email `%s` is an a gateway account address', $email));
            } else {
                $person->addEmailAddress($this->findOrCreatePersonEmail($email));
                $this->logInfo(sprintf(
                    $num ? 'Set email `%s`' : 'Set primary email `%s`',
                    $entity->getFirstEmail()
                ));
            }
        }
        foreach ($entity->getUserGroups() as $user_group) {
            $person->addUsergroup($this->getUserGroupMapper()->findOneByTitle($user_group));
        }
        foreach ($entity->getCustomFields() as $custom_field) {
            $person->addCustomData($this->createCustomData($custom_field));
        }

        $this->records->add($person);
        return $this->records;
    }

    /**
     * Returns a person entity
     * Creates a new person if not found
     *
     * @param array $emails
     *
     * @return DeskPROEntity\Person
     * @throws \Exception
     */
    private function findOrCreatePerson(array $emails)
    {
        $emails = array_values($emails);
        if (empty($emails)) {
            throw new ImporterException('Unable to find or create without primary email');
        }

        $person = $this->getPersonMapper()->findOneByEmails($emails, false);
        if ($person) {
            $this->logNotice(sprintf(
                'Found existing user, id=`%d` with email `%s`',
                $person->getId(), $person->getEmailAddress()
            ));
        } else {
            $person = new DeskPROEntity\Person();
            $this->logWarning(sprintf('Creating new person with email `%s`', $emails[0]));
        }

        return $person;
    }

    /**
     * Returns a person email entity
     *
     * @param string $email_string
     * @return DeskPROEntity\PersonEmail
     */
    private function findOrCreatePersonEmail($email_string)
    {
        $email = $this->getPersonEmailMapper()->findOneByEmail($email_string, false);
        if ($email) {
            $this->logInfo(sprintf(
                'Found existing person email, id=`%d` with email `%s`',
                $email->getId(), $email->getEmail()
            ));
        } else {
            $email = new DeskPROEntity\PersonEmail();
            $email
                ->setEmail($email_string)
                ->setIsValidated(true);

            $this->records->add($email);
            $this->logWarning(sprintf('Creating new person email `%s`', $email->getEmail()));
        }

        return $email;
    }

    /**
     * Returns custom def person entity
     *
     * @param Entity\CustomField $importing_entity
     *
     * @return DeskPROEntity\CustomDataPerson
     * @throws ImporterException
     */
    private function createCustomData(Entity\CustomField $importing_entity)
    {
        $person_def = $this->getCustomDefPersonMapper()->findOneByTitle($importing_entity->getKey());
        $entity     = new DeskPROEntity\CustomDataPerson();

        switch ($person_def->getTypeName()) {
            case Entity\CustomField::FIELD_TYPE_TEXT:
            case Entity\CustomField::FIELD_TYPE_TEXTAREA:
                $entity
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($importing_entity->getValue());

                break;

            case Entity\CustomField::FIELD_TYPE_TOGGLE:
                $entity
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($importing_entity->getValue() ? 1 : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_DATE:
                $entity
                    ->setField($person_def)
                    ->setRootField($person_def)
                    ->setValue($importing_entity->getValue() ? strtotime($importing_entity->getValue()) : 0);

                break;

            case Entity\CustomField::FIELD_TYPE_CHOICE:
                // todo choice def from persons or tickets
                $choice_def = $this->getCustomDefPersonMapper()->findOneByTitle($importing_entity->getValue());
                $entity
                    ->setField($choice_def)
                    ->setRootField($person_def)
                    ->setValue(1);

                break;

            default:
                throw new ImporterException('Unknown custom field type `%s`', $person_def->getTypeName());
        }

        $this->records->add($entity);
        return $entity;
    }

    /**
     * Returns the person email mapper
     *
     * @return Mapper\PersonEmail
     * @throws \Exception
     */
    private function getPersonEmailMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_PERSON_EMAIL);
    }

    /**
     * Returns the email account mapper
     *
     * @return Mapper\EmailAccount
     * @throws \Exception
     */
    private function getEmailAccountMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_EMAIL_ACCOUNT);
    }

    /**
     * Returns the user group mapper
     *
     * @return Mapper\UserGroup
     * @throws \Exception
     */
    private function getUserGroupMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_USER_GROUP);
    }

    /**
     * Returns the custom def person mapper
     *
     * @return Mapper\CustomDefPerson
     * @throws \Exception
     */
    private function getCustomDefPersonMapper()
    {
        return $this->mappers->getMapperByType(Mapper\MapperInterface::TYPE_CUSTOM_DEF_PERSON);
    }
}
