<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\AbstractPhoneNumber;
use Application\DeskPRO\Entity\ContactDataAbstract;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\AbstractContactData;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Address;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Facebook;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\InstantMessage;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\LinkedIn;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Twitter;
use DeskPRO\Bundle\ImportBundle\Model\ContactData\Website;
use DeskPRO\Bundle\ImportBundle\Model\ContactDataAwareModelInterface;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperInterface;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\MapperRegistry;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Orb\Util\PhoneNumbers;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;

/**
 * Class ContactDataHandler.
 */
abstract class AbstractContactDataHelper
{
    /**
     * @var CreateEntityHelper
     */
    protected $createEntityHelper;

    /**
     * @var EntityPersister
     */
    protected $persister;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var MapperRegistry
     */
    protected $mapperRegistry;

    /**
     * Constructor.
     *
     * @param MapperRegistry     $mapperRegistry
     * @param CreateEntityHelper $createEntityHelper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        MapperRegistry     $mapperRegistry,
        CreateEntityHelper $createEntityHelper,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        $this->mapperRegistry     = $mapperRegistry;
        $this->createEntityHelper = $createEntityHelper;
        $this->persister          = $persister;
        $this->logger             = $logger;
    }

    /**
     * @param ContactDataAwareModelInterface $model
     * @param mixed                          $entity
     */
    public function updateContactData(ContactDataAwareModelInterface $model, $entity)
    {
        $contactData = $model->getContactData() ?: new ContactData();

        // update address contacts
        foreach ($contactData->getAddress() as $contactModel) {
            $this->createOrUpdateAddress($contactModel, $entity);
        }

        // update facebook contacts
        foreach ($contactData->getFacebook() as $contactModel) {
            $this->createOrUpdateFacebook($contactModel, $entity);
        }

        // update instant message contacts
        foreach ($contactData->getInstantMessage() as $contactModel) {
            $this->createOrUpdateInstantMessage($contactModel, $entity);
        }

        // update linkedin contacts
        foreach ($contactData->getLinkedIn() as $contactModel) {
            $this->createOrUpdateLinkedIn($contactModel, $entity);
        }

        // update phone contacts
        foreach ($contactData->getPhone() as $contactModel) {
            try {
                $phoneNumber = PhoneNumberUtil::getInstance()->parse($contactModel->getNumber(), null);
            } catch (\Exception $e) {
                $phoneNumber = null;
                $this->logger->warning("Phone number `{$contactModel->getNumber()}` is invalid");
            }

            if ($phoneNumber) {
                $this->createOrUpdatePhoneNumber($phoneNumber, $contactModel, $entity);
            }
        }

        // update twitter contacts
        foreach ($contactData->getTwitter() as $contactModel) {
            $this->createOrUpdateTwitter($contactModel, $entity);
        }

        // update website contacts
        foreach ($contactData->getWebsite() as $contactModel) {
            $this->createOrUpdateWebsite($contactModel, $entity);
        }
    }

    /**
     * @param Address $contactModel
     * @param mixed   $entity
     */
    protected function createOrUpdateAddress(Address $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity
            ->setField1($contactModel->getAddress())
            ->setField2($contactModel->getCity())
            ->setField3($contactModel->getState())
            ->setField4($contactModel->getZip())
            ->setField5($contactModel->getCountry())
        ;

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * @param Facebook $contactModel
     * @param mixed    $entity
     */
    protected function createOrUpdateFacebook(Facebook $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($contactModel->getUrl());

        if (preg_match('#/profile\.php?id=([0-9]+)#', $contactModel->getUrl(), $m)) {
            $contactEntity->setField2($m[1]);
        } elseif (preg_match('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $contactModel->getUrl(), $m)) {
            $contactEntity->setField2($m[1]);
        } elseif (preg_match('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $contactModel->getUrl(), $m)) {
            $contactEntity->setField2($m[1]);
        }

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * @param InstantMessage $contactModel
     * @param mixed          $entity
     */
    protected function createOrUpdateInstantMessage(InstantMessage $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($contactModel->getUsername());
        $contactEntity->setField2($contactModel->getService());

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * @param LinkedIn $contactModel
     * @param mixed    $entity
     */
    protected function createOrUpdateLinkedIn(LinkedIn $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($contactModel->getUrl());
        $contactEntity->setField2(Strings::extractRegexMatch('#/in/(.*?)$#', $contactModel->getUrl(), 1) ?: '');

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * @param Twitter $contactModel
     * @param mixed   $entity
     */
    protected function createOrUpdateTwitter(Twitter $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($contactModel->getUsername());
        $contactEntity->setField2((int) $contactModel->isDisplayFeed());

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * @param Website $contactModel
     * @param mixed   $entity
     */
    protected function createOrUpdateWebsite(Website $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($contactModel->getUrl());

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }

    /**
     * {@inheritdoc}
     */
    protected function createOrUpdatePhoneNumber(PhoneNumber $phoneNumber, Phone $contactModel, $entity)
    {
        /** @var AbstractPhoneNumber $contactEntity */
        $contactEntity = $this->createEntityHelper->findOrCreateEntity($this->getPhoneNumberMapper(), $contactModel);
        $contactEntity
            ->setNumber($contactModel->getNumber())
            ->setRegion(PhoneNumberUtil::getInstance()->getRegionCodeForNumber($phoneNumber))
            ->setLabel($contactModel->getType() ?: 'phone')
            ->setGuessedType(PhoneNumbers::getTypeCode($contactModel->getNumber()))
            ->setOwner($entity)
        ;

        // prevent dupes
        foreach ($entity->getPhoneNumbers() as $existingPhone) {
            if ($existingPhone->getNumber() === $contactEntity->getNumber()) {
                return;
            }
        }

        $this->persister->persistAndFlush($contactEntity, $contactModel);
    }

    /**
     * @return MapperInterface
     */
    abstract protected function getContactDataMapper();

    /**
     * @return MapperInterface
     */
    abstract protected function getPhoneNumberMapper();

    /**
     * @param AbstractContactData $contactModel
     *
     * @return ContactDataAbstract
     */
    protected function findOrCreateContactEntity(AbstractContactData $contactModel)
    {
        /** @var ContactDataAbstract $entity */
        $entity = $this->createEntityHelper->findOrCreateEntity($this->getContactDataMapper(), $contactModel);
        $entity->setContactType($contactModel->getContactType());
        $entity->setComment($contactModel->getComment());

        return $entity;
    }

    /**
     * @param ContactDataAbstract $contactEntity
     * @param AbstractContactData $contactModel
     * @param mixed               $entity
     */
    protected function persistAndFlushContactEntity(ContactDataAbstract $contactEntity, AbstractContactData $contactModel, $entity)
    {
        // prevent dupes
        foreach ($entity->getContactData() as $existingContact) {
            /** @var ContactDataAbstract $existingContact */
            if ($existingContact->getSearchString() === $contactEntity->getSearchString()) {
                return;
            }
        }

        $this->logger->debug("Add new contact {$contactEntity->getContactType()}");
        $entity->addContactData($contactEntity);

        $this->persister->persistAndFlush($contactEntity, $contactModel);
    }
}
