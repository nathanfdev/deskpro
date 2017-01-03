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

namespace Application\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\ImportBundle\Model\ContactData\AbstractContactData;
use Application\ImportBundle\Model\ContactData\ContactData;
use Application\ImportBundle\Model\ContactDataAwareModelInterface;
use Application\ImportBundle\Writer\EntityPersister;
use Application\ImportBundle\Writer\Mapper\MapperInterface;
use Doctrine\Common\Collections\ArrayCollection;
use libphonenumber\PhoneNumberUtil;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;

/**
 * Class ContactDataHandler.
 */
class ContactDataHelper
{
    /**
     * @var CreateEntityHelper
     */
    private $createEntityHelper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param CreateEntityHelper $createEntityHelper
     * @param EntityPersister    $persister
     * @param LoggerInterface    $logger
     */
    public function __construct(CreateEntityHelper $createEntityHelper, EntityPersister $persister, LoggerInterface $logger)
    {
        $this->createEntityHelper = $createEntityHelper;
        $this->persister          = $persister;
        $this->logger             = $logger;
    }

    /**
     * @param MapperInterface                $mapper
     * @param ContactDataAwareModelInterface $model
     * @param mixed                          $entity
     */
    public function updateContactData(MapperInterface $mapper, ContactDataAwareModelInterface $model, $entity)
    {
        // prepare new contact data collection
        $newContactData = new ArrayCollection();
        $contactData    = $model->getContactData() ?: new ContactData();

        foreach ($contactData->getAddress() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity
                ->setField1($contactModel->getAddress())
                ->setField2($contactModel->getCity())
                ->setField3($contactModel->getState())
                ->setField4($contactModel->getZip())
                ->setField5($contactModel->getCountry())
            ;

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        foreach ($contactData->getFacebook() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity->setField1($contactModel->getUrl());

            if (preg_match('#/profile\.php?id=([0-9]+)#', $contactModel->getUrl(), $m)) {
                $contactEntity->setField2($m[1]);
            } elseif (preg_match('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $contactModel->getUrl(), $m)) {
                $contactEntity->setField2($m[1]);
            } elseif (preg_match('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $contactModel->getUrl(), $m)) {
                $contactEntity->setField2($m[1]);
            }

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        foreach ($contactData->getInstantMessage() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity->setField1($contactModel->getUsername());
            $contactEntity->setField2($contactModel->getService());

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        foreach ($contactData->getLinkedIn() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity->setField1($contactModel->getUrl());
            $contactEntity->setField2(Strings::extractRegexMatch('#/in/(.*?)$#', $contactModel->getUrl(), 1) ?: '');

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        foreach ($contactData->getPhone() as $contactModel) {
            try {
                $phoneNumberUtil = PhoneNumberUtil::getInstance();
                $number          = $phoneNumberUtil->parse($contactModel->getNumber(), null);

                $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
                $contactEntity->setField1($number->getCountryCode());
                $contactEntity->setField2($number->getNationalNumber());
                $contactEntity->setField3($contactModel->getType() ?: 'phone');

                // set searchable value
                $contactEntity->setField9($contactModel->getNumber());

                $newContactData->add($contactEntity);
                $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
            } catch (\Exception $e) {
                $this->logger->warning("Phone number `{$contactModel->getNumber()}` is invalid");
            }
        }

        foreach ($contactData->getTwitter() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity->setField1($contactModel->getUsername());
            $contactEntity->setField2((int) $contactModel->isDisplayFeed());

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        foreach ($contactData->getWebsite() as $contactModel) {
            $contactEntity = $this->findOrCreateContactEntity($contactModel, $mapper);
            $contactEntity->setField1($contactModel->getUrl());

            $newContactData->add($contactEntity);
            $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
        }

        // remove deleted contacts
        /** @var ContactDataAbstract $contactEntity */
        foreach ($entity->getContactData() as $contactEntity) {
            if (!$newContactData->contains($contactEntity)) {
                $this->logger->debug("Remove deleted contact {$contactEntity->getContactType()}");
                $entity->getContactData()->removeElement($contactEntity);
                $this->persister->removeAndFlush($contactEntity);
            }
        }
    }

    /**
     * @param AbstractContactData $contactModel
     * @param MapperInterface     $mapper
     *
     * @return ContactDataAbstract
     */
    private function findOrCreateContactEntity(AbstractContactData $contactModel, MapperInterface $mapper)
    {
        /** @var ContactDataAbstract $entity */
        $entity = $this->createEntityHelper->findOrCreateEntity($mapper, $contactModel);
        $entity->setContactType($contactModel->getContactType());
        $entity->setComment($contactModel->getComment());

        return $entity;
    }

    /**
     * @param ContactDataAbstract $contactEntity
     * @param AbstractContactData $contactModel
     * @param mixed               $entity
     */
    private function persistAndFlushContactEntity(ContactDataAbstract $contactEntity, AbstractContactData $contactModel, $entity)
    {
        if (!$entity->getContactData()->contains($contactEntity)) {
            $this->logger->debug("Add new contact {$contactEntity->getContactType()}");
            $entity->addContactData($contactEntity);
        }

        $this->persister->persistAndFlush($contactEntity, $contactModel);
    }
}
