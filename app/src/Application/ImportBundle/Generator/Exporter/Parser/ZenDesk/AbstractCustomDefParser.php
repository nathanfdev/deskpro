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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\DeskPRO\Entity\CustomDefAbstract;
use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;

/**
 * Class AbstractCustomDefParser
 * @package Application\ImportBundle\Generator\Exporter\Parser\ZenDesk
 */
abstract class AbstractCustomDefParser extends AbstractParser
{
    /**
     * @param array $options
     * @return Entity\Collection|Entity\AbstractCustomDef[]
     */
    protected function exportCustomFieldOptions(array $options)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($options)
            ->setPrefix('ZDTicketCustomDefSystemOption')
            ->setRefColumn('id')
            ->setMethod('exportCustomFieldOption')
        ;

        return $this->exportCollection($config);
    }

    /**
     * @param array $data
     * @param int   $num
     *
     * @return Entity\AbstractCustomDef
     */
    protected function exportCustomFieldOption(array $data, $num)
    {
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_' . $num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'ticket_custom_def_',
                'ref'    => 'id',
            )),
            'name'        => TransformerInterface::TYPE_STRING,
            'value'       => TransformerInterface::TYPE_STRING,
        ));

        $entity = new Entity\TicketCustomDef();
        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['value'])
            ->setDescription($formatted['name'])
            ->setHandlerClass(CustomDefAbstract::HANDLER_CLASS_CHOICE)
            ->setAsEnabled(true)
        ;

        return $entity;
    }
}
