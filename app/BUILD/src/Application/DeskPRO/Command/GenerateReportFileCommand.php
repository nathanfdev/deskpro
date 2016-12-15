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

/**
 * DeskPRO.
 *
 * @category Commands
 */
namespace Application\DeskPRO\Command;

use Application\DeskPRO\ServerReportFile\ServerReportFile;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateReportFileCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    const ARG_FILECHECK = 'without-filecheck';

    protected function configure()
    {
        $this->setDefinition(array(
            new InputOption(self::ARG_FILECHECK, null, InputOption::VALUE_NONE, 'Disables files integrity check.'),
        ))->setName('dp:generate-report-file');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $srf = new ServerReportFile(
            $this->getContainer()->get('doctrine.orm.entity_manager'),
            $output
        );

        $prop = new \ReflectionProperty($srf, 'files_added_to_archive');
        unset($srf->files_added_to_archive['phpinfo-web.html']);

        if ($input->getOption(self::ARG_FILECHECK)) {
            unset($srf->files_added_to_archive['file-integrity.txt']);
        }

        $file = $srf->createArchive();

        $output->writeln(sprintf('Report file saved to: %s', $file));
    }
}
