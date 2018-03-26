<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

/**
 * Class Facebook.
 */
class Facebook extends AbstractUrlContactData
{
    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'facebook';
    }
}
