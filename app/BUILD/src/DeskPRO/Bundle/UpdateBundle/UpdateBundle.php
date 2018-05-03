<?php

namespace DeskPRO\Bundle\UpdateBundle;

use DeskPRO\Bundle\UpdateBundle\Command as UpdateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class UpdateBundle.
 */
class UpdateBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function registerCommands(Application $application)
    {
        $application->add(new UpdateCommand\Dev\GenBuildManifestCommand());
        $application->add(new UpdateCommand\Dev\GenBuildScriptCommand());
        $application->add(new UpdateCommand\Dev\MoveBuildScriptsCommand());

        $application->add(new UpdateCommand\Tasks\RunBuildCommand());
        $application->add(new UpdateCommand\Tasks\RunCommand());
        $application->add(new UpdateCommand\Tasks\RunSyncCommand());
        $application->add(new UpdateCommand\Tasks\StatusCommand());

        $application->add(new UpdateCommand\ActivateBuildCommand());
        $application->add(new UpdateCommand\UpdateCommand());
        $application->add(new UpdateCommand\UpdateStatusCommand());
        $application->add(new UpdateCommand\DbBackupCommand());
        $application->add(new UpdateCommand\DownloadBuildCommand());
        $application->add(new UpdateCommand\ResetCommand());
        $application->add(new UpdateCommand\StatusCommand());
        $application->add(new UpdateCommand\UpdateCleanupCommand());
        $application->add(new UpdateCommand\FixSchemaCommand());
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
