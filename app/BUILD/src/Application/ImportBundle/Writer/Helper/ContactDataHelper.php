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

namespace Application\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\ContactDataAbstract;
use Application\ImportBundle\Model\ContactData\AbstractContactData;
use Application\ImportBundle\Model\ContactData\ContactData;
use Doctrine\Common\Collections\ArrayCollection;
use Orb\Util\Strings;

/**
 * Class ContactDataHandler.
 */
class ContactDataHelper
{
    /**
     * @var string
     */
    private $entityClassName;

    /**
     * Constructor.
     *
     * @param string $entityClassName
     */
    public function __construct($entityClassName)
    {
        $this->entityClassName = $entityClassName;
    }

    /**
     * @param ContactData $model
     *
     * @return ArrayCollection|ContactDataAbstract[]
     */
    public function getEntities(ContactData $model)
    {
        $collection = new ArrayCollection();

        foreach ($model->getAddress() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getAddress());
            $entity->setField2($contact->getCity());
            $entity->setField3($contact->getState());
            $entity->setField4($contact->getZip());
            $entity->setField5($contact->getCountry());

            $collection->add($entity);
        }

        foreach ($model->getFacebook() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getUrl());

            if (preg_match('#/profile\.php?id=([0-9]+)#', $contact->getUrl(), $m)) {
                $entity->setField2($m[1]);
            } elseif (preg_match('#facebook\.com/([a-zA-Z0-9\.\-_]+)#', $contact->getUrl(), $m)) {
                $entity->setField2($m[1]);
            } elseif (preg_match('#facebook\.com/people/([a-zA-Z0-9\.\-_]+)#', $contact->getUrl(), $m)) {
                $entity->setField2($m[1]);
            }

            $collection->add($entity);
        }

        foreach ($model->getInstantMessage() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getUsername());
            $entity->setField2($contact->getService());

            $collection->add($entity);
        }

        foreach ($model->getLinkedIn() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getUrl());
            $entity->setField2(Strings::extractRegexMatch('#/in/(.*?)$#', $contact->getUrl(), 1) ?: '');

            $collection->add($entity);
        }

        foreach ($model->getPhone() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getCode());
            $entity->setField2($contact->getNumber());
            $entity->setField3($contact->getType() ?: 'phone');

            $collection->add($entity);
        }

        foreach ($model->getTwitter() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getUsername());
            $entity->setField2((int) $contact->isDisplayFeed());

            $collection->add($entity);
        }

        foreach ($model->getWebsite() as $contact) {
            $entity = $this->createContactTypeEntity($contact);
            $entity->setField1($contact->getUrl());

            $collection->add($entity);
        }

        return $collection;
    }

    /**
     * @param AbstractContactData $contact
     *
     * @return ContactDataAbstract
     */
    private function createContactTypeEntity(AbstractContactData $contact)
    {
        /** @var ContactDataAbstract $entity */
        $entity = new $this->entityClassName();
        $entity->setContactType($contact->getContactType());
        $entity->setComment($contact->getComment());

        return $entity;
    }
}
