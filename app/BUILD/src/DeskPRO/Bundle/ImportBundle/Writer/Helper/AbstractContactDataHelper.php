<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

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
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
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
     * @var MapperInterface
     */
    protected $contactDataMapper;

    /**
     * Constructor.
     *
     * @param MapperInterface    $contactDataMapper
     * @param CreateEntityHelper $createEntityHelper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(
        MapperInterface    $contactDataMapper,
        CreateEntityHelper $createEntityHelper,
        EntityPersister    $persister,
        LoggerInterface    $logger
    ) {
        $this->contactDataMapper  = $contactDataMapper;
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
     * @param PhoneNumber $phoneNumber
     * @param Phone       $contactModel
     * @param mixed       $entity
     */
    abstract protected function createOrUpdatePhoneNumber(PhoneNumber $phoneNumber, Phone $contactModel, $entity);

    /**
     * @param AbstractContactData $contactModel
     *
     * @return ContactDataAbstract
     */
    protected function findOrCreateContactEntity(AbstractContactData $contactModel)
    {
        /** @var ContactDataAbstract $entity */
        $entity = $this->createEntityHelper->findOrCreateEntity($this->contactDataMapper, $contactModel);
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
