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
        // Csv reader
        $definition = new Definition('Application\ImportBundle\CsvReader\CsvReader');
        $container->setDefinition('deskpro.import.csv_reader', $definition);

        // Json reader
        $definition = new Definition('Application\ImportBundle\JsonReader\JsonReader');
        $container->setDefinition('deskpro.import.json_reader', $definition);

        // OsTicket reader
        $definition = new Definition('Application\ImportBundle\OsTicket\OsTicketReaderFactory');
        $container->setDefinition('deskpro.import.os_ticket_reader_factory', $definition);

        $definition = new Definition('Application\ImportBundle\OsTicket\OsTicketReader');
        $definition->setFactoryService('deskpro.import.os_ticket_reader_factory');
        $definition->setFactoryMethod('createReaderByDeskproConfig');
        $container->setDefinition('deskpro.import.os_ticket_reader', $definition);

        // ZenDesk reader
        $definition = new Definition('Application\ImportBundle\ZenDesk\ZenDeskReader');
        $container->setDefinition('deskpro.import.zen_desk_reader', $definition);

        // Import generator
        $definition = new Definition('Application\ImportBundle\Generator\GeneratorFactory');
        $definition->addArgument(new Reference('service_container'));
        $container->setDefinition('deskpro.import.generator_factory', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Generator');
        $definition->setFactoryService('deskpro.import.generator_factory');
        $definition->setFactoryMethod('createGenerator');
        $container->setDefinition('deskpro.import.generator', $definition);
    }
}
