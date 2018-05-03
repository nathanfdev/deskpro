<?php

namespace DeskPRO\Bundle\InstallBundle\Installer\InstallStep;

use Symfony\Component\Console\Question\Question;

/**
 * Checks server requirements.
 *
 * Note that this only checks our current environemnt. We will
 * need to check TWICE more in later steps:
 *  a) Check the web env
 *  b) Check the cli env when spawned from ourselves
 */
class OwnRequirementsStep extends AbstractStep
{
    public function run()
    {
        $f = $this->getFormatterHelper();
        $this->writeBigTitle('Server Requirements');

        /** @var \DpSys\SoftwareRequirements\DeskproRequirements $checker */
        $checker = require DP_APP_DIR.'/sys/SoftwareRequirements/load_checker.php';

        $error_count = 0;
        $warn_count  = 0;

        /** @var \DpSys\SoftwareRequirements\Requirement $req */
        foreach ($checker->getRequirements() as $req) {
            if ($req->isFulfilled()) {
                continue;
            }

            $this->writeln($f->formatSection('Error', $req->getTestMessage(), 'error'));
            $this->writeln($req->getHelpText());
            $this->writeln('');
            ++$error_count;
        }

        /** @var \DpSys\SoftwareRequirements\Requirement $req */
        foreach ($checker->getRecommendations() as $req) {
            if ($req->isFulfilled()) {
                continue;
            }

            $this->writeln($f->formatSection('Recommendation', $req->getTestMessage(), 'info'));
            $this->writeln($req->getHelpText());
            $this->writeln('');
            ++$warn_count;
        }

        $ini_path = $checker->getPhpIniConfigPath();
        if (!$ini_path) {
            $msg =
                'WARNING: PHP is not configured to use a php.ini file. This usually means that '
                .'PHP is not configured at all, and may be missing may features or extensions.';
            $this->writeln($f->formatSection('Recommendation', 'No php.ini file', 'info'));
            $this->writeln($msg);
            $this->writeln('');
            ++$warn_count;
        }

        if ($warn_count) {
            $this->writeln(sprintf('<info>There are %d recommendations that you may wish to implement.</info>', $warn_count));
            $this->writeln('These recommendations are optional but are encouraged to ensure the best operation of your helpdesk.');
            $this->writeln('');
        }

        if ($error_count) {
            $this->writeln(sprintf('<error>There are %d failed requirements that require your attention.</error>', $error_count));
            $this->writeln('DeskPRO CANNOT be installed until you fix these errors.');
            $this->writeln('');
            $this->markAsFailed();
        }

        if ($ini_path && ($warn_count || $error_count)) {
            $this->writeln('If you need to make changes to PHP configuration, here is the path to your php.ini file:');
            $this->writeln("<comment>$ini_path</comment>");
            $this->writeln('');
        }

        if (!$error_count && $warn_count && $this->getContext()->getSession()->getSource() !== 'buildserver') {
            $this->writeln('Do you want to skip these recommendations and continue with the install?');

            if (($res = $this->getContext()->getProfile()->getAnswer('skip_recommendations'))) {
                $this->writeln('> '.$res);
                $res = 'Y';
            } else {
                $q   = new Question('[Y/n]> ', 'Y');
                $res = $this->getQuestionHelper()->ask($this->getInput(), $this->getOutput(), $q);
            }

            if (strtoupper($res) !== 'Y') {
                $this->markAsFailed();
            }
        }

        if (!$this->isFailed()) {
            $this->getSession()->enableFlag('own_requirements_check');
        }
    }

    public function isComplete()
    {
        return $this->getSession()->hasFlag('own_requirements_check');
    }
}
