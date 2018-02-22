<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\UpdateBundle;

use DeskPRO\Bundle\UpdateBundle\Command as UpdateCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class UpdateBundle extends Bundle
{
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
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
