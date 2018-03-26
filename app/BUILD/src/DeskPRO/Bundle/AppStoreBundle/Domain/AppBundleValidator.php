<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface AppBundleValidator
{
    /**
     * @param AppBundle $bundle
     * @return boolean
     */
    public function validateBundle(AppBundle $bundle);

}
