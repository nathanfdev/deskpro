<?php

namespace DpSys\Boot\BootTask;

use Symfony\Component\HttpFoundation\Request;

class HttpVerifyRequirementsBootTask
{
    public function run(\DpRun\DpEnv $env, array $resources)
    {
        // If installed, we render check requirements
        if (!$env->getConfig('database.host') && !$env->getConfig('database.0.host')) {
            $this->renderServerCheckPage();
            exit;
        }

        if ($env->getConfig('env.skip_req_check')) {
            return;
        }

        // small optimisation, dont run req checker
        // on ajax/api requests where we couldnt see the result anyway
        if (!empty($resources['request'])) {
            /** @var Request $request */
            $request = $resources['request'];
            if ($request->isXmlHttpRequest()) {
                return;
            } elseif (strpos($request->getPathInfo(), '/api/') === 0) {
                return;
            }
        }

        /** @var \DpSys\SoftwareRequirements\DeskproRequirements $checker */
        $checker = require $env->getAppDir().'/sys/SoftwareRequirements/load_checker.php';

        if (count($checker->getFailedRequirements())) {
            echo "This server does not meet the minimum server requirements required by DeskPRO.\n\n";
            echo "Execute the dp:web-server-info command from the command-line to get the URL to your requirements status page.\n";
            echo "Refer to this article on usage: https://support.deskpro.com/en/kb/articles/553\n";
            exit(0);
        }
    }

    protected function renderServerCheckPage()
    {
        $checker = require __DIR__.'/../../SoftwareRequirements/load_checker.php';

        if (isset($_GET['encode-output'])) {
            header('Content-Type: text/plain');
            echo str_repeat('-', 25).'BEGIN'.str_repeat('-', 25).PHP_EOL;
            echo base64_encode(serialize($checker));
            echo PHP_EOL;
            echo str_repeat('-', 25).'END'.str_repeat('-', 25).PHP_EOL;
            exit;
        }

        $majorProblems = $checker->getFailedRequirements();
        $minorProblems = $checker->getFailedRecommendations();
        require __DIR__.'/../../Resources/views/requirements.php';
    }
}
