<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

namespace Application\ImportBundle\Command;

use Application\ImportBundle\Generator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ImportCommand
 * @package Application\ImportBundle\Command
 */
class ImportCommand extends AbstractExportCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this->setName('dp:import:run');
        $this->setHelp("Executes the importer.");

        parent::configure();
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);

        $config = $this->createGeneratorConfig($input);
        $config->setWriterType(Generator\Writer\WriterInterface::TYPE_DESK_PRO);
        if (!$config->getInputPath()) {
            throw new \Exception('Input path must be set up');
        }

        $logger    = $this->createLogger($config, $output);
        $generator = $this->createGenerator($config, $output, $logger);

        try {
            $generator->generate();
            $output->writeln('');
            $output->writeln(sprintf(
                'Done. Importing was successful. Look at the log file `%s` to see details.',
                $config->getLogPath()
            ));

        } catch (Generator\GeneratorException $e) {
            $output->writeln('');
            foreach ($e->getExceptions() as $exception) {
                /** @var Generator\Validator\ValidatorConstraintException $exception */
                $logger->critical($exception);
            }
            if ($config->isVerbose() === false) {
                $output->writeln(sprintf(
                    'An error has occurred while importing. Look at the log file `%s` to see details.',
                    $config->getLogPath()
                ));
            }

        } catch (\Exception $e) {
            $logger->critical($e->getMessage());
            $output->writeln(sprintf(
                'An error has occurred while importing. Look at the log file `%s` to see details.',
                $config->getLogPath()
            ));
        }
    }
}
