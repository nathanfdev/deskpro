<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

use DeskPRO\Bundle\ImportBundle\Model\ContactData\ContactData;

/**
 * Interface ContactDataAwareModelInterface.
 */
interface ContactDataAwareModelInterface
{
    /**
     * Returns organization contact data.
     *
     * @return ContactData
     */
    public function getContactData();
}
