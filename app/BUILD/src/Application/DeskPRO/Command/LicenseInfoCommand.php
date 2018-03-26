<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use DpSys\License;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LicenseInfoCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:license-info');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lic = License::getLicense();

        if ($lic->isLicenseCodeError()) {
            echo 'License Error: '.$lic->getLicenseCodeError();

            return 1;
        }

        echo 'License ID: '.$lic->getLicenseId();
        echo "\n";

        echo 'Expires: ';
        if ($lic->getExpireDate()) {
            echo $lic->getExpireDate()->format('Y-m-d H:i:s');

            $diff = $lic->getExpireDate()->getTimestamp() - time();
            if ($diff < 1) {
                echo ' (EXPIRED)';
            } else {
                echo ' ('.\Orb\Util\Dates::secsToReadable($diff, 3).')';
            }
        } else {
            echo 'Never';
        }
        echo "\n";

        echo 'Agents: ';
        if ($lic->getMaxAgents()) {
            echo $lic->getMaxAgents();
        } else {
            echo 'Unlimited';
        }
        echo "\n";

        return 0;
    }
}
