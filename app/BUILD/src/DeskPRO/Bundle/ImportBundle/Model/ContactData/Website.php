<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

/**
 * Class Website.
 */
class Website extends AbstractUrlContactData
{
    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'website';
    }
}
