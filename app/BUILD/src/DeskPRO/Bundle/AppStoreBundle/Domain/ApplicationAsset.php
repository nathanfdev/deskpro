<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface ApplicationAsset
{
    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getRawContent();
}
