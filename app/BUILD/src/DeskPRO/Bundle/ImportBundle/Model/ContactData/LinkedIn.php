<?php

namespace DeskPRO\Bundle\ImportBundle\Model\ContactData;

/**
 * Class LinkedIn.
 */
class LinkedIn extends AbstractUrlContactData
{
    /**
     * {@inheritdoc}
     */
    public function getContactType()
    {
        return 'linked_in';
    }
}
