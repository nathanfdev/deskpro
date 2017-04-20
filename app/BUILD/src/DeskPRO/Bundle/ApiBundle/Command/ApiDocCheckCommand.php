<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Command;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\ApiDocValidator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ApiDocCheckCommand.
 */
class ApiDocCheckCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:apidoc-check');
        $this->setDescription('Verifies api doc annotations');
        $this->addOption('strict', null, InputOption::VALUE_NONE, 'Check in strict mode');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $apiDocExtractor = $this->getContainer()->get('nelmio_api_doc.extractor.api_doc_extractor');
        $apiDocValidator = new ApiDocValidator();

        $strict    = $input->getOption('strict');
        $extracted = $apiDocExtractor->extractAnnotations($apiDocExtractor->getRoutes());
        $failures  = $apiDocValidator->validate($extracted, $strict);

        if (count($failures)) {
            foreach ($failures as $failure) {
                $output->writeln(sprintf(
                    '%s %s: missing %s',
                    $failure['path'], $failure['method'], implode(', ', $failure['missing'])
                ));
            }
        } else {
            $output->writeln('All fine.');
        }
    }
}
