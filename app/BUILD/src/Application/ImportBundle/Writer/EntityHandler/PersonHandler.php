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
use Application\ImportBundle\Model;
use Application\ImportBundle\Writer\Helper\ContactDataHelper;
use Application\ImportBundle\Writer\Helper\CustomDataHelper;
use Application\ImportBundle\Writer\Helper\LabelHelper;

/**
 * DeskPRO person importer.
 *
 * Class Person
 */
class PersonHandler extends AbstractEntityHandler
{
    /**
     * {@inheritdoc}
     */
    public static function getModelClass()
    {
        return Model\Person::class;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(Model\ImportModelInterface $model, $entityId = null)
    {
        if (!$model instanceof Model\Person) {
            Model\UnexpectedException::throwUnexpectedEntityTypeException($model);
        }

        if ($model->isAgent()) {
            $this->logger->alert(sprintf('Importing agent `%s`', $model->getFirstEmail()));
        }

        $entity = $this->findOrCreatePerson($model->getEmails());
        $entity
            ->setFirstName($model->getFirstName())
            ->setLastName($model->getLastName())
            ->setName($model->getName())
            ->setTimezone($model->getTimezone())
            ->setIsAgent($model->isAgent())
            ->setCanAgent($model->isAgent())
            ->setCanAdmin($model->isAdmin())
            ->setLanguage($model->getLanguage() ? $this->findLanguage($model->getLanguage()) : null)
            ->setIsDisabled($model->isDisabled())
            ->setIsDeleted($model->isDeleted())
            ->setDateCreated($model->getDateCreated())
            ->setOrganization($this->findOrCreateOrganization($model->getOrganization()))
            ->setOrganizationPosition($model->getOrganizationPosition())
            ->resetEmails()
            ->resetUsergroups()
            ->resetContactData()
        ;

        if ($model->isAgent() && !in_array('agent_all_safe_perms', $model->getUserGroups(), true)) {
            $model->addUserGroup('agent_all_safe_perms');
        }

        if ($model->getPassword()) {
            if ($model->isPlainPasswordScheme()) {
                $entity->setPassword($model->getPassword());
            } else {
                $this->logger->alert(sprintf('Password scheme `%s` is not supported. Set initial password.', $model->getPasswordScheme()));
                $entity->setPassword(Model\Person::INITIAL_PASSWORD);
            }
        } else {
            if ($model->isUser()) {
                $entity->setPassword(Model\Person::INITIAL_PASSWORD);
            }
        }

        foreach ($model->getEmails() as $email) {
            if ($this->mappers->getEmailAccountMapper()->findOneByEmail($email, false)) {
                $this->logger->warning(sprintf('Email `%s` is an a gateway account address (Skipping)', $email));
            } else {
                if (!count($entity->getEmailAddresses())) {
                    $entity->setEmail($email);
                    $this->logger->debug(sprintf('Set primary email `%s`', $model->getFirstEmail()));
                } else {
                    $entity->addEmailAddressString($email);
                    $this->logger->debug(sprintf('Set email `%s`', $model->getFirstEmail()));
                }
            }
        }
        foreach ($model->getUserGroups() as $user_group_name) {
            $user_group = $this->findUserGroup($user_group_name);
            if ($user_group) {
                $entity->addUsergroup($user_group);
            }
        }
        foreach ($this->createContactData($model) as $contactEntity) {
            $entity->addContactData($contactEntity);
        }

        $labelsHelper = new LabelHelper($this->logger);
        $labelsHelper->updateLabels($model, $entity, DeskPROEntity\LabelPerson::class);

        $customDataHelper = new CustomDataHelper($this->mappers->getPersonCustomDefMapper(), $this->logger);
        $customDataHelper->updateCustomData($model, $entity, $this->records);

        $this->records->setPrimaryEntity($entity);
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

        $entity = $this->mappers->getPersonMapper()->findOneByEmails($emails, false);
        if ($entity) {
            $this->logger->debug(sprintf(
                'Found existing user, id=`%d` with email `%s`',
                $entity->getId(), $entity->getEmailAddress()
            ));
        } else {
            $entity = new DeskPROEntity\Person();
            $this->logger->info(sprintf('Creating new person with email `%s`', $emails[0]));
        }

        return $entity;
    }

    /**
     * Returns person contact data entity.
     *
     * @param Model\Person $model
     *
     * @return DeskPROEntity\PersonContactData[]
     */
    private function createContactData(Model\Person $model)
    {
        return (new ContactDataHelper(DeskPROEntity\PersonContactData::class))->getEntities($model->getContactData());
    }
}
