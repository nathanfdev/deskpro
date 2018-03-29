<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\EntityHandler;

use Application\DeskPRO\Entity;
use DeskPRO\Bundle\ImportBundle\Model;
use Orb\Util\Strings;

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
     *
     * @param Model\Person $model
     */
    public function writeModel(Model\PrimaryImportModelInterface $model, $brandName = null)
    {
        if ($model->isAgent()) {
            $this->logger->alert(sprintf('Importing agent `%s`', $model->getFirstEmail()));
        }

        $entity = $this->findOrCreatePerson($model);
        $entity
            ->setTimezone($model->getTimezone())
            ->setIsAgent($model->isAgent())
            ->setCanAgent($model->isAgent())
            ->setCanAdmin($model->isAdmin())
            ->setLanguage($this->helpers->getLanguageHelper()->findOrCreateLanguage($model->getLanguage()))
            ->setIsDisabled($model->isDisabled())
            ->setIsDeleted($model->isDeleted())
            ->setTitlePrefix($model->getTitlePrefix())
        ;

        if ($model->getDateCreated()) {
            $entity->setDateCreated($model->getDateCreated());
        }

        // update person name
        if ($model->getFirstName()) {
            $entity->setFirstName($model->getFirstName());
        }
        if ($model->getLastName()) {
            $entity->setLastName($model->getLastName());
        }
        if ($model->getName()) {
            $entity->setName($model->getName());
        }

        // no name provided, email fallback
        if (!$entity->getName() && $model->getFirstEmail()) {
            $entity->setName(Strings::getNameFromEmail($model->getFirstEmail()));
        }

        // update organization
        if ($model->getOrganization()) {
            $organizationEntity = $this->helpers->getOrganizationHelper()->findOrCreateOrganization($model->getOrganization());
            if ($organizationEntity) {
                $entity->setOrganization($organizationEntity);
                $entity->setOrganizationPosition($model->getOrganizationPosition());
            } else {
                $entity->setOrganization(null);
            }
        }

        // set or reset password if we get it from the import
        if ($model->getPassword()) {
            $entity->setPassword($model->getPassword());
        }

        // update person emails
        foreach ($model->getEmails() as $email) {
            if ($this->mappers->getEmailAccountMapper()->findOneByEmail($email)) {
                $this->logger->warning(sprintf('Email `%s` is an a gateway account address (Skipping)', $email));
                continue;
            }

            if (!in_array($email, $entity->getEmailAddresses(false))) {
                $entity->addEmailAddressString($email);
            }
        }

        foreach ($entity->getEmails() as $emailEntity) {
            if (!in_array($emailEntity->getEmail(), $model->getEmails())) {
                $this->logger->debug("`{$emailEntity->getEmail()}` email was not found in the new email list, removing.");
                $entity->getEmails()->removeElement($emailEntity);
                if ($entity->getPrimaryEmailAddress() === $emailEntity->getEmail()) {
                    $entity->setPrimaryEmail(null);
                }
            }
        }

        // update primary email if it was unset during 'emails' collection update
        if (!$entity->getPrimaryEmail() && $entity->getEmails()->count()) {
            $entity->setPrimaryEmail($entity->getEmails()->first());
        }

        // update common props
        $this->helpers->getUserGroupHelper()->updateUserGroupsByModel($model, $entity);
        $this->helpers->getUserGroupHelper()->updateAgentGroupsByModel($model, $entity);
        $this->helpers->getCustomDataHelper()->updateCustomData($this->mappers->getPersonCustomDefMapper(), $model, $entity);
        $this->helpers->getLabelHelper()->updateLabels($model, $entity, Entity\LabelPerson::class);

        // persist basic entity
        $this->persister->persistAndFlush($entity, $model);

        // persist others related entities which contains own oids
        $this->helpers->getPersonContactDataHelper()->updateContactData($model, $entity);
    }

    /**
     * Returns a person entity.
     * Creates a new person if not found.
     *
     * @param Model\Person $model
     *
     * @return Entity\Person
     */
    private function findOrCreatePerson(Model\Person $model)
    {
        $entity = null;

        // try to find existing person by emails
        if (count($model->getEmails())) {
            $entity = $this->mappers->getPersonMapper()->findOneByEmails($model->getEmails());
            if ($entity) {
                $this->logger->debug(sprintf(
                    'Found existing user, id=`%d` with email `%s`',
                    $entity->getId(), $entity->getEmailAddress()
                ));
            }
        }

        // find person by oid or create a new one
        if (!$entity) {
            $entity = $this->findOrCreateEntity($this->mappers->getPersonMapper(), $model);
        }

        return $entity;
    }
}
