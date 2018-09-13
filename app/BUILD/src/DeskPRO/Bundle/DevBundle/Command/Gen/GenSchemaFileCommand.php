<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Command\Gen;

use DeskPRO\Bundle\InstallBundle\Schema\SchemaGenerator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenSchemaFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('dpdev:gen:schema-files');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->generateSchemaForManager('default', $output, true);
        $this->generateSchemaForManager('system', $output);
        $this->generateSchemaForManager('audit', $output);
        $this->generateSchemaForManager('voice', $output);
    }

    /**
     * @param string          $name           EntityManager name
     * @param OutputInterface $output
     * @param bool            $isMasterSchema Master schema is schema containing all non-entity tables
     */
    private function generateSchemaForManager($name, OutputInterface $output, $isMasterSchema = false)
    {
        /* @var \DpRun\DpEnv $DP_ENV */
        global $DP_ENV;

        $em = $this->getContainer()->get('doctrine')->getManager($name);

        $output->writeln('Generating map... This might take a while.');
        $startTime = microtime(true);

        $gen = new SchemaGenerator($em);

        $output->writeln(sprintf('Generated schema of %d artefacts %.3fs', $gen->count(), microtime(true) - $startTime));

        $writePath = $DP_ENV->getAppBaseKernelCacheDir().DIRECTORY_SEPARATOR.$name.'_deskpro_schema.php';
        $gen->dumpToFile($writePath, $isMasterSchema);
        $output->writeln(sprintf('Wrote schema to: <info>%s</info>', $writePath));
    }
}
