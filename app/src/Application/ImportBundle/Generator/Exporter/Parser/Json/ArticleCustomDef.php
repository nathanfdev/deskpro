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

namespace Application\ImportBundle\Generator\Exporter\Parser\Json;

use Application\ImportBundle\Entity;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerConfiguration;
use Application\ImportBundle\Generator\Exporter\Formatter\Transformer\TransformerInterface;
use Application\ImportBundle\Generator\Exporter\Parser\ExportCollectionConfig;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Class ArticleCustomDef
 * @package Application\ImportBundle\Generator\Exporter\Parser\Json
 */
final class ArticleCustomDef extends AbstractParser
{
    /**
     * {@inheritdoc}
     */
    public function getEntityType()
    {
        return Entity\EntityInterface::TYPE_ARTICLE_CUSTOM_DEF;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount()
    {
        return $this->reader->getDirectoryFilesCount(JsonReaderInterface::ENTITY_ARTICLE_CUSTOM_DEF_PATH, $this->getBatchNum());
    }

    /**
     * {@inheritdoc}
     */
    public function export()
    {
        $config = new ExportCollectionConfig();
        $config
            ->setData($this->reader->getData(JsonReaderInterface::ENTITY_ARTICLE_CUSTOM_DEF_PATH, $this->getBatchNum()))
            ->setPrefix('JSONArticleCustomDef')
            ->setRefColumn('oid')
            ->setMethod('exportCustomDef')
            ->setAdvanceProgressbar(true)
        ;

        return $this->exportCollection($config);
    }

    /**
     * @param array $data
     * @return Entity\TicketCustomDef
     */
    protected function exportCustomDef(array $data)
    {
        $formatted = $this->formatter->format($data, array(
            'oid'           => TransformerInterface::TYPE_STRING,
            'destination'   => TransformerConfiguration::create(TransformerInterface::TYPE_DESTINATION, array(
                'prefix' => 'person_custom_def_',
                'ref'    => 'oid',
            )),
            'sys_name'      => TransformerInterface::TYPE_STRING,
            'parent_id'     => TransformerInterface::TYPE_STRING,
            'title'         => TransformerInterface::TYPE_STRING,
            'description'   => TransformerInterface::TYPE_STRING,
            'handler_class' => TransformerInterface::TYPE_STRING,
            'is_enabled'    => TransformerInterface::TYPE_BOOLEAN,
            'options'       => TransformerInterface::TYPE_ARRAY,
        ));

        $entity = new Entity\PersonCustomDef();
        $entity
            ->setRawData($data)
            ->setOid($formatted['oid'])
            ->setDestination($formatted['destination'])
            ->setSysName($formatted['sys_name'])
            ->setTitle($formatted['title'])
            ->setDestination($formatted['description'])
            ->setHandlerClass($formatted['handler_class'])
            ->setAsEnabled($formatted['is_enabled'])
            ->setOptions($formatted['options'])
        ;

        return $entity;
    }
}
