<?php

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
