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

namespace DeskPRO\Bundle\ImportBundle\Model;

use JMS\Serializer\Annotation as JMS;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CustomDataAwareTrait.
 */
trait CustomDataAwareTrait
{
    /**
     * @var array
     *
     * @JMS\Type("array<DeskPRO\Bundle\ImportBundle\Model\CustomField>")
     *
     * @Assert\Valid()
     */
    protected $custom_fields = [];

    /**
     * {@inheritdoc}
     */
    public function getCustomFields()
    {
        return $this->custom_fields;
    }

    /**
     * @param array $customFields
     *
     * @return $this
     */
    public function setCustomFields(array $customFields)
    {
        $this->custom_fields = $customFields;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addCustomField(CustomField $custom_field)
    {
        $this->custom_fields[] = $custom_field;

        return $this;
    }
}
