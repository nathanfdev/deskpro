<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\AppEnv;

/**
 * The app environment is access to the low-level environment including paths
 * and filesystem configuration.
 *
 * NOTE: This is a wrapper around the global \DpRun\DpEnv that is created on every PHP script
 * via app/run/init_env.php.
 *
 * The reason this is a separate class is so we have a clear separation between "pre-boot"
 * code and container code. AppEnv is added to the container so we dont have lots of globals access
 * all over the place, makes it easier to manage.
 */
class AppEnv implements AppEnvInterface
{
    /**
     * @var \DpRun\DpEnv
     */
    private $dpEnv;

    /**
     * AppEnv constructor.
     *
     * @param \DpRun\DpEnv $dpEnv
     */
    public function __construct(\DpRun\DpEnv $dpEnv)
    {
        $this->dpEnv = $dpEnv;
    }

    /**
     * {@inheritdoc}
     */
    public function getDpRoot()
    {
        return $this->dpEnv->getDpRoot();
    }

    /**
     * {@inheritdoc}
     */
    public function getWwwRoot()
    {
        return $this->dpEnv->getWwwRoot();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppWwwAssetDir()
    {
        return $this->dpEnv->getAppWwwAssetDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppName()
    {
        return $this->dpEnv->getAppName();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppDir()
    {
        return $this->dpEnv->getAppDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getAppBaseKernelCacheDir()
    {
        return $this->dpEnv->getAppBaseKernelCacheDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserTmpDir()
    {
        return $this->dpEnv->getUserTmpDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserCacheDir()
    {
        return $this->dpEnv->getUserCacheDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserLogsDir()
    {
        return $this->dpEnv->getUserLogsDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserDebugDir()
    {
        return $this->dpEnv->getUserDebugDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserBackupsDir()
    {
        return $this->dpEnv->getUserBackupsDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFilesDir()
    {
        return $this->dpEnv->getUserFilesDir();
    }

    /**
     * {@inheritdoc}
     */
    public function getEnvId()
    {
        return $this->dpEnv->getEnvId();
    }

    /**
     * {@inheritdoc}
     */
    public function isDebug()
    {
        return $this->dpEnv->isDebug();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig($id, $default = null)
    {
        return $this->dpEnv->getConfig($id, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function findConfigFile($f)
    {
        return $this->dpEnv->findConfigFile($f);
    }

    /**
     * {@inheritdoc}
     */
    public function setRuntimeVar($name, $value)
    {
        $this->dpEnv->setRuntimeVar($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function hasRuntimeVar($name)
    {
        return $this->dpEnv->hasRuntimeVar($name);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetRuntimeVar($name)
    {
        $this->dpEnv->unsetRuntimeVar($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRuntimeVar($name, $default = '__throw__')
    {
        return $this->dpEnv->getRuntimeVar($name, $default);
    }
}
