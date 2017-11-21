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

namespace DeskPRO\Bundle\AppStoreBundle\Domain\AppManifestChanges;

use DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\CustomField;
use JMS\Serializer\Annotation as JMS;

/**
 * @JMS\ExclusionPolicy("all")
 */
class CustomFieldChange
{
    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $module = 'customFields';

    /**
     * @JMS\Type("string")
     * @JMS\Expose()
     *
     * @var string
     */
    private $type;

    /**
     * @JMS\Type("DeskPRO\Bundle\AppStoreBundle\Domain\AppManifest\CustomField")
     * @JMS\Expose()
     *
     * @var CustomField
     */
    private $value;

    /**
     * @param $changeType
     * @param CustomField $value
     */
    public function __construct($changeType, CustomField $value)
    {
        $this->type  = $changeType;
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return CustomField
     */
    public function getValue()
    {
        return $this->value;
    }
}
