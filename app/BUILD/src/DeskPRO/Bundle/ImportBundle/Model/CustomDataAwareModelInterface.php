<?php

namespace DeskPRO\Bundle\ImportBundle\Model;

/**
 * Interface CustomDataOwnerModelInterface.
 */
interface CustomDataAwareModelInterface
{
    /**
     * @return CustomField[]
     */
    public function getCustomFields();

    /**
     * @param CustomField $custom_field
     *
     * @return $this
     */
    public function addCustomField(CustomField $custom_field);
}
