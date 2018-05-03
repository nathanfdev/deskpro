<?php

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
