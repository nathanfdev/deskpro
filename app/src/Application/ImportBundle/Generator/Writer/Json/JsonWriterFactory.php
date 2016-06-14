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

namespace Application\ImportBundle\Generator\Writer\Json;

use Application\ImportBundle\Entity\EntityInterface;
use Application\ImportBundle\Generator\Writer\AbstractWriterFactory;
use Application\ImportBundle\Reader\Json\JsonReaderInterface;

/**
 * Generator json writer factory.
 *
 * Class JsonWriterFactory
 */
class JsonWriterFactory extends AbstractWriterFactory
{
    /**
     * {@inheritdoc}
     */
    public function createWriter()
    {
        $mapping = new DestinationCollection();
        $mapping
            ->attach(new Destination(EntityInterface::TYPE_ARTICLE, JsonReaderInterface::ENTITY_ARTICLE_PATH))
            ->attach(new Destination(EntityInterface::TYPE_ARTICLE_CATEGORY, JsonReaderInterface::ENTITY_ARTICLE_CATEGORY_PATH))
            ->attach(new Destination(EntityInterface::TYPE_ARTICLE_CUSTOM_DEF, JsonReaderInterface::ENTITY_ARTICLE_CUSTOM_DEF_PATH))
            ->attach(new Destination(EntityInterface::TYPE_DOWNLOAD, JsonReaderInterface::ENTITY_DOWNLOAD_PATH))
            ->attach(new Destination(EntityInterface::TYPE_FEEDBACK, JsonReaderInterface::ENTITY_FEEDBACK_PATH))
            ->attach(new Destination(EntityInterface::TYPE_FEEDBACK_CUSTOM_DEF, JsonReaderInterface::ENTITY_FEEDBACK_CUSTOM_DEF_PATH))
            ->attach(new Destination(EntityInterface::TYPE_NEWS, JsonReaderInterface::ENTITY_NEWS_PATH))
            ->attach(new Destination(EntityInterface::TYPE_PERSON, JsonReaderInterface::ENTITY_PERSON_PATH))
            ->attach(new Destination(EntityInterface::TYPE_PERSON_CUSTOM_DEF, JsonReaderInterface::ENTITY_PERSON_CUSTOM_DEF_PATH))
            ->attach(new Destination(EntityInterface::TYPE_TICKET, JsonReaderInterface::ENTITY_TICKET_PATH))
            ->attach(new Destination(EntityInterface::TYPE_TICKET_CUSTOM_DEF, JsonReaderInterface::ENTITY_TICKET_CUSTOM_DEF_PATH))
            ->attach(new Destination(EntityInterface::TYPE_ORGANIZATION, JsonReaderInterface::ENTITY_ORGANIZATION_PATH))
            ->attach(new Destination(EntityInterface::TYPE_ORGANIZATION_CUSTOM_DEF, JsonReaderInterface::ENTITY_ORGANIZATION_CUSTOM_DEF_PATH))
        ;

        return new JsonWriter($mapping);
    }
}
