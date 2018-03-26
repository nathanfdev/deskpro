<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO;

use Application\DeskPRO\DependencyInjection\AppSecretPass;
use Application\DeskPRO\DependencyInjection\Compiler\AppVariablePass;
use Application\DeskPRO\DependencyInjection\CoreExtension;
use Application\DeskPRO\DependencyInjection\DoctrineEntityListenerPass;
use Application\DeskPRO\DependencyInjection\ElasticaClientPass;
use Application\DeskPRO\DependencyInjection\SearchExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DeskPROBundle extends \Symfony\Component\HttpKernel\Bundle\Bundle
{
    public function __construct()
    {
        $this->name = 'DeskPRO';
    }

    public function build(ContainerBuilder $container)
    {
        // register the extension(s) found in DependencyInjection/ directory
        parent::build($container);

        $container->registerExtension(new CoreExtension());
        $container->registerExtension(new SearchExtension());
        $container->addCompilerPass(new AppVariablePass());
        $container->addCompilerPass(new AppSecretPass());
        $container->addCompilerPass(new ElasticaClientPass());
        $container->addCompilerPass(new DoctrineEntityListenerPass());
    }

    /**
     * @param Application $application An Application instance
     */
    public function registerCommands(Application $application)
    {
        $commands = [
            'Application\\DeskPRO\\Command\\AgentsCommand',
            'Application\\DeskPRO\\Command\\AsseticCommand',
            'Application\\DeskPRO\\Command\\CheckBlobStorageCommand',
            'Application\\DeskPRO\\Command\\CollectEmailCommand',
            'Application\\DeskPRO\\Command\\DbCollationChangeCommand',
            'Application\\DeskPRO\\Command\\DecodeTacCommand',
            'Application\\DeskPRO\\Command\\DefaultDataCommand',
            'Application\\DeskPRO\\Command\\DevCommand',
            'Application\\DeskPRO\\Command\\DevGenDpqlDocsCommand',
            'Application\\DeskPRO\\Command\\DevTestApiCommand',
            'Application\\DeskPRO\\Command\\FixBlobPathsCommand',
            'Application\\DeskPRO\\Command\\GenerateSchemaFileCommand',
            'Application\\DeskPRO\\Command\\GenRandomEmailCommand',
            'Application\\DeskPRO\\Command\\IndexElasticsearchCommand',
            'Application\\DeskPRO\\Command\\InternalUpgradeRunnerCommand',
            'Application\\DeskPRO\\Command\\JobExecuteCommand',
            'Application\\DeskPRO\\Command\\JobSupervisorCommand',
            'Application\\DeskPRO\\Command\\LicenseInfoCommand',
            'Application\\DeskPRO\\Command\\LoginTokenCommand',
            'Application\\DeskPRO\\Command\\MoveBlobsCommand',
            'Application\\DeskPRO\\Command\\OptimisePermsCommand',
            'Application\\DeskPRO\\Command\\PopulateElasticsearchCommand',
            'Application\\DeskPRO\\Command\\ProcessEmailCommand',
            'Application\\DeskPRO\\Command\\RefillTicketActiveCommand',
            'Application\\DeskPRO\\Command\\SchemaCommand',
            'Application\\DeskPRO\\Command\\SearchReindexCommand',
            'Application\\DeskPRO\\Command\\TestCommand',
            'Application\\DeskPRO\\Command\\TestEmailDecodeCommand',
            'Application\\DeskPRO\\Command\\TestFilterCommand',
            'Application\\DeskPRO\\Command\\VerifyBlobsCommand',
            'Application\\DeskPRO\\Command\\WorkerJobCommand',
        ];

        foreach ($commands as $cmd) {
            $application->add(new $cmd());
        }
    }

    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    public function getPath()
    {
        return __DIR__;
    }
}
