<?php

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

/**
 * Class OrganizationContactDataHelper.
 */
class OrganizationContactDataHelper extends AbstractContactDataHelper
{
    /**
     * {@inheritdoc}
     */
    protected function getContactDataMapper()
    {
        return $this->mapperRegistry->getOrganizationContactDataMapper();
    }

    /**
     * {@inheritdoc}
     */
    protected function getPhoneNumberMapper()
    {
        return $this->mapperRegistry->getOrganizationPhoneNumberMapper();
    }
}
