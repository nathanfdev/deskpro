<?php

/**
 * DeskPRO.
 *
 * @category Entities
 */

namespace Application\DeskPRO\App;

use Application\DeskPRO\Entity\AppInstance;
use Application\DeskPRO\Entity\AppPackage;

interface AppManagerInterface
{
    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasPackage($name);

    /**
     * @param string $name
     *
     * @throws \InvalidArgumentException
     *
     * @return AppPackage
     */
    public function getPackage($name);

    /**
     * @return AppPackage[]
     */
    public function getAllPackages();

    /**
     * @param int $id
     *
     * @return bool
     */
    public function hasApp($id);

    /**
     * @param int $id
     *
     * @throws \InvalidArgumentException
     *
     * @return AppInstance
     */
    public function getApp($id);

    /**
     * @return AppInstance[]
     */
    public function getAllApps();

    /**
     * @param string $name The package name
     *
     * @return AppInstance[]
     */
    public function getPackageApps($name);
}
