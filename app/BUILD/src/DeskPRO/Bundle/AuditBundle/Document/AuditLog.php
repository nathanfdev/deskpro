<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AuditBundle\Document;

use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AuditBundle\Entity\AuditLog as AuditLogEntity;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;

/**
 * Class Test.
 *
 * @ODM\Document()
 * @JMS\ExclusionPolicy("all")
 */
class AuditLog extends AuditLogEntity implements EntityInterface
{
    /**
     * @ODM\Id(strategy="INCREMENT")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $id;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $action;

    /**
     * @ODM\Field(type="date")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTime")
     * @JMS\Groups("list")
     *
     * @var \DateTime
     */
    protected $dateCreated;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $performerName;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $performerId;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectName;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("list")
     *
     * @var string
     */
    protected $objectType;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $objectId;

    /**
     * @ODM\Field(type="string")
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups("details")
     *
     * @var string
     */
    protected $description;

    /**
     * @ODM\Field(type="int")
     *
     * @JMS\Expose()
     * @JMS\Type("integer")
     * @JMS\Groups("list")
     *
     * @var int
     */
    protected $apiKey;

    /**
     * @ODM\EmbedOne(targetDocument="DeskPRO\Bundle\AuditBundle\Document\AuditLogData")
     * @JMS\Groups("details")
     *
     * @var AuditLogData
     */
    protected $data;
}
