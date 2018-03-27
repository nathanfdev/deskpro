<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use DeskPRO\Bundle\ImportBundle\Model\ContactData\Phone;
use libphonenumber\PhoneNumber;

/**
 * Class OrganizationContactDataHelper.
 */
class OrganizationContactDataHelper extends AbstractContactDataHelper
{
    /**
     * {@inheritdoc}
     */
    protected function createOrUpdatePhoneNumber(PhoneNumber $phoneNumber, Phone $contactModel, $entity)
    {
        $contactEntity = $this->findOrCreateContactEntity($contactModel);
        $contactEntity->setField1($phoneNumber->getCountryCode());
        $contactEntity->setField2($phoneNumber->getNationalNumber());
        $contactEntity->setField3($contactModel->getType() ?: 'phone');

        // set searchable value
        $contactEntity->setField9($contactModel->getNumber());

        $this->persistAndFlushContactEntity($contactEntity, $contactModel, $entity);
    }
}
