<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

namespace Application\ImportBundle\Generator\Exporter\Parser\ZenDesk;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\ZenDesk\ZenDeskReaderInterface;

/**
 * Abstract ZenDesk custom def parser.
 *
 * Class AbstractCustomDefParser
 */
abstract class AbstractCustomDefParser extends AbstractParser
{
    /**
     * Return a collection of choice options.
     *
     * @param array $options
     *
     * @return Entity\Collection|Entity\AbstractCustomDef[]
     */
    protected function exportCustomFieldOptions(array $options)
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($options)
            ->setPrefix('ZDCustomDefOption')
            ->setRefColumn('id')
            ->setMethod('exportCustomFieldOption')
        ;

        return $this->exportCollection($config);
    }

    /**
     * Returns choice option.
     *
     * @param array $data
     * @param int   $num
     *
     * @return Entity\AbstractCustomDef
     */
    protected function exportCustomFieldOption(array $data, $num)
    {
        $entity    = $this->getDefaultCustomDefEntity();
        $formatted = $this->formatter->format($data, array(
            'id'          => TransformerConfiguration::create(TransformerInterface::TYPE_STRING, array(
                'default' => 'num_'.$num,
            )),
            'destination' => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix'  => $entity->getDestinationPrefix(),
                'ref'     => 'id',
            )),
            'name'  => TransformerInterface::TYPE_STRING,
            'value' => TransformerInterface::TYPE_STRING,
        ));

        $entity
            ->setRawData($data)
            ->setOid($formatted['id'])
            ->setDestination($formatted['destination'])
            ->setTitle($formatted['name'])
            ->setDescription('')
            ->setAsEnabled(true)
        ;

        return $entity;
    }

    /**
     * Creates custom def options.
     *
     * @param array $formatted
     * @param array $options
     *
     * @return array
     */
    protected function configureOptions(array $formatted, array $options = array())
    {
        switch ($formatted['type']) {
            case ZenDeskReaderInterface::FIELD_TYPE_REGEXP:
                $regex = $formatted['regexp_for_validation'];

                // No delims
                if ($regex && $regex[0] != substr($regex, -1, 1)) {
                    $regex = '/'.$regex.'/';
                }

                $options = array_merge($options, array(
                    'validation_type'       => 'regex',
                    'regex'                 => $regex,
                    'agent_validation_type' => 'regex',
                    'agent_regex'           => $regex,
                ));

                break;

            case ZenDeskReaderInterface::FIELD_TYPE_DECIMAL:
                $decimal_regex = '/^[-+]?[0-9]*[.,]?[0-9]+$/';
                $options       = array_merge($options, array(
                    'validation_type'       => 'regex',
                    'regex'                 => $decimal_regex,
                    'agent_validation_type' => 'regex',
                    'agent_regex'           => $decimal_regex,
                ));

                break;

            case ZenDeskReaderInterface::FIELD_TYPE_INTEGER:
                $numeric_regex = '/^[-+]?\d+$/';
                $options       = array_merge($options, array(
                    'validation_type'       => 'regex',
                    'regex'                 => $numeric_regex,
                    'agent_validation_type' => 'regex',
                    'agent_regex'           => $numeric_regex,
                ));

                break;
        }

        return $options;
    }

    /**
     * Returns empty custom def entity.
     *
     * @return Entity\AbstractCustomDef
     */
    abstract protected function getDefaultCustomDefEntity();
}
