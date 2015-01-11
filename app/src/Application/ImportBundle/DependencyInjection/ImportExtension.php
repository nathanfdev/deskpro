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

        $this->setReaders($container);
        $this->setExporters($container);
        $this->setValidators($container);
        $this->setWriter($container);
        $this->setGenerator($container);
    }

    /**
     * Set readers
     *
     * @param ContainerBuilder $container
     */
    private function setReaders(ContainerBuilder $container)
    {
        $definition = new Definition('Application\ImportBundle\CsvReader\CsvReader');
        $container->setDefinition('deskpro.import.csv_reader', $definition);

        $definition = new Definition('Application\ImportBundle\OsTicket\OsTicketReader');
        $definition->addArgument(null);
        $container->setDefinition('deskpro.import.os_ticket_reader', $definition);
    }

    /**
     * Set exporters
     *
     * @param ContainerBuilder $container
     */
    public function setExporters(ContainerBuilder $container)
    {
        $definition = new Definition('Application\ImportBundle\Generator\Exporter\CsvFactory');
        $definition->addArgument(new Reference('service_container'));
        $container->setDefinition('deskpro.import.generator.exporter.csv_factory', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Exporter\OsTicketFactory');
        $definition->addArgument(new Reference('service_container'));
        $container->setDefinition('deskpro.import.generator.exporter.osticket_factory', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Exporter\Csv');
        $definition->setFactoryService('deskpro.import.generator.exporter.csv_factory');
        $definition->setFactoryMethod('createExporter');
        $container->setDefinition('deskpro.import.generator.exporter.csv', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Exporter\OsTicket');
        $definition->setFactoryService('deskpro.import.generator.exporter.osticket_factory');
        $definition->setFactoryMethod('createExporter');
        $container->setDefinition('deskpro.import.generator.exporter.osticket', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Exporter\Collection');
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.exporter.csv')));
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.exporter.osticket')));
        $container->setDefinition('deskpro.import.generator.exporter.collection', $definition);
    }

    /**
     * Set validators
     *
     * @param ContainerBuilder $container
     */
    private function setValidators(ContainerBuilder $container)
    {
        $definition = new Definition('Application\ImportBundle\Generator\Validator\Person');
        $definition->addArgument(new Reference('validator'));
        $container->setDefinition('deskpro.import.generator.validator.person', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Validator\Ticket');
        $definition->addArgument(new Reference('validator'));
        $container->setDefinition('deskpro.import.generator.validator.ticket', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Validator\Collection');
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.validator.person')));
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.validator.ticket')));
        $container->setDefinition('deskpro.import.generator.validator.collection', $definition);
    }

    /**
     * Set writer
     *
     * @param ContainerBuilder $container
     */
    private function setWriter(ContainerBuilder $container)
    {
        $definition = new Definition('Application\ImportBundle\Generator\Writer\Json\Destination\Person');
        $container->setDefinition('deskpro.import.generator.writer.json.destination.person', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Writer\Json\Destination\Ticket');
        $container->setDefinition('deskpro.import.generator.writer.json.destination.ticket', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Writer\Json\Destination\Collection');
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.writer.json.destination.person')));
        $definition->addMethodCall('attach', array(new Reference('deskpro.import.generator.writer.json.destination.ticket')));
        $container->setDefinition('deskpro.import.generator.writer.json.destination.collection', $definition);

        $definition = new Definition('Application\ImportBundle\Generator\Writer\Json\JsonWriter');
        $definition->addArgument(new Reference('deskpro.import.generator.writer.json.destination.collection'));
        $container->setDefinition('deskpro.import.generator.writer.json', $definition);
    }

    /**
     * Set generator
     *
     * @param ContainerBuilder $container
     */
    private function setGenerator(ContainerBuilder $container)
    {
        $definition = new Definition('Application\ImportBundle\Generator\Generator');
        $definition->addArgument(new Reference('deskpro.import.generator.writer.json'));
        $definition->addArgument(new Reference('deskpro.import.generator.exporter.collection'));
        $definition->addArgument(new Reference('deskpro.import.generator.validator.collection'));
        $container->setDefinition('deskpro.import.generator', $definition);
    }
}
