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

namespace Application\ImportBundle\Generator;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class GeneratorFactory
 * @package Application\ImportBundle\Generator
 */
class GeneratorFactory
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return GeneratorInterface
     */
    public function createGenerator()
    {
        return new Generator(
            $this->createJsonWriter(),
            $this->createExportersCollection(),
            $this->createValidatorsCollection()
        );
    }

    /**
     * Create a json writer
     *
     * @return Writer\Json\JsonWriter
     */
    public function createJsonWriter()
    {
        $mapping = new Writer\Json\Destination\Collection();
        $mapping
            ->attach(new Writer\Json\Destination\Person())
            ->attach(new Writer\Json\Destination\Ticket());

        return new Writer\Json\JsonWriter($mapping);
    }

    /**
     * Returns a collection of exporters
     *
     * @return Exporter\Collection
     */
    private function createExportersCollection()
    {
        $scvReaderFactory      = new Exporter\CsvFactory($this->container);
        $osTicketReaderFactory = new Exporter\OsTicketFactory($this->container);

        $exporters = new Exporter\Collection();
        $exporters
            ->attach($scvReaderFactory->createExporter())
            ->attach($osTicketReaderFactory->createExporter());

        return $exporters;
    }

    /**
     * Returns a collection of validators
     *
     * @return Validator\Collection
     */
    private function createValidatorsCollection()
    {
        /** @var \Symfony\Component\Validator\Validator $validator */
        $validator  = $this->container->get('validator');
        $validators = new Validator\Collection();
        $validators
            ->attach(new Validator\Person($validator))
            ->attach(new Validator\Ticket($validator));

        return $validators;
    }
}
