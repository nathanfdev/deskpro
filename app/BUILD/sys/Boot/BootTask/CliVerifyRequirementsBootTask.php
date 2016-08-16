<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpSys\Boot\BootTask;

class CliVerifyRequirementsBootTask
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        if ($env->getConfig('env.skip_req_check')) {
            return;
        }

        /** @var \DpSys\SoftwareRequirements\DeskproRequirements $checker */
        $checker = require $env->getAppDir().'/sys/SoftwareRequirements/load_checker.php';

        if (count($checker->getFailedRequirements())) {
            echo "The version of PHP are you using to run this command has failed the requirements check:\n\n";

            /** @var \DpSys\SoftwareRequirements\Requirement $r */
            foreach ($checker->getFailedRequirements() as $r) {
                echo '- '.$r->getHelpText()."\n";
            }

            echo "\n";
            echo "For full details, execute the following command:\n";
            echo escapeshellarg($env->getConfig('paths.php_path')).' '.$env->getDpRoot().DIRECTORY_SEPARATOR.'bin'.DIRECTORY_SEPARATOR.'check_requirements';
            echo "\n";

            exit(1);
        }
    }
}
