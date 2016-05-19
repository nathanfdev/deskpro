<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use JMS\Serializer\Annotation as JMS;
use Orb\Types\JsonObjectSerializable;

/**
 * Class AuditLogData.
 *
 * @ODM\EmbeddedDocument()
 * @JMS\ExclusionPolicy("none")
 */
class AuditLogData implements JsonObjectSerializable
{
    /**
     * @ODM\Field(type="hash")
     *
     * @JMS\Type("array")
     * @JMS\Groups("details")
     *
     * @var array
     */
    private $context;

    /**
     * @ODM\Field(type="hash")
     *
     * @JMS\Type("array")
     * @JMS\Groups("details")
     *
     * @var array
     */
    private $diff;

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @param mixed $context
     *
     * @return $this
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiff()
    {
        return $this->diff;
    }

    /**
     * @param mixed $diff
     *
     * @return $this
     */
    public function setDiff($diff)
    {
        $this->diff = $diff;

        return $this;
    }

    /**
     * @return array
     */
    public function serializeJsonArray()
    {
        return [
            'context' => $this->context,
            'diff'    => $this->diff,
        ];
    }

    /**
     * @param array $data
     *
     * @return AuditLogData
     */
    public static function unserializeJsonArray(array $data)
    {
        $obj = new self();
        $obj->setDiff($data['diff'])->setContext($data['context']);

        return $obj;
    }
}
