<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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

namespace Application\ImportBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ImportExtension
 * @package Application\ImportBundle\DependencyInjection
 */
class ImportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
//        $os_config = dp_get_config('osticket_import');
//
//        $db_host = $os_config['db_host'];
//        $db_name = $os_config['db_name'];
//        $db_username = $os_config['db_username'];
//        $db_password = $os_config['db_password'];
//
//        $db = new \PDO("mysql:dbname={$db_name};host={$db_host}", $db_username, $db_password);

        $definition = new Definition('Application\ImportBundle\CsvReader\CsvReader');
        $container->setDefinition('deskpro.import.csv_reader', $definition);

        $definition = new Definition('Application\ImportBundle\OsTicket\OsTicketReader');
        $definition->addArgument(null);
        $container->setDefinition('deskpro.import.os_ticket_reader', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\GeneratorFactory');
        $definition->addArgument(new Reference('service_container'));
        $container->setDefinition('deskpro.import.generator_factory', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Generator');
        $definition->setFactoryService('deskpro.import.generator_factory');
        $definition->setFactoryMethod('createGenerator');
        $container->setDefinition('deskpro.import.generator', $definition);
    }
}
