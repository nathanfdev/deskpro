<?php

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
