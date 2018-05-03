<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

/**
 * Representation of a Deskpro app store application.
 */
interface Application
{
    /**
     * Returns the system identifier assigned to the application.
     *
     * @return string
     */
    public function getId();

    /**
     * Returns the name given by the owner.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the manifest.
     *
     * @return AppManifest
     */
    public function getManifest();
}
