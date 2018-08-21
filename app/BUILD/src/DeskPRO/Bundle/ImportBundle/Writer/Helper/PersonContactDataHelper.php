<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

/**
 * Class PersonContactDataHelper.
 */
class PersonContactDataHelper extends AbstractContactDataHelper
{
    /**
     * {@inheritdoc}
     */
    protected function getContactDataMapper()
    {
        return $this->mapperRegistry->getPersonContactDataMapper();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPhoneNumberMapper()
    {
        return $this->mapperRegistry->getPersonPhoneNumberMapper();
    }
}
