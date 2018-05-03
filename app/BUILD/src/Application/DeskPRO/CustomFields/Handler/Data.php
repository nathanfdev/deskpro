<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * A field that doesnt have any user-editable form field. It's used by API's or other features to store
 * data attached to things. For example, storing data from a user source on a person.
 */
class Data extends HandlerAbstract
{
    /**
     * @param array $formData
     * @param null  $default
     *
     * @return mixed|null
     */
    private function findValue(array $formData, $default = null)
    {
        $names = $this->getAllFormFieldNames();
        foreach ($names as $name) {
            if (isset($formData[$name])) {
                return $formData[$name];
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $valueIfNotPresent = new \stdClass();
        $value             = $this->findValue($formData, $valueIfNotPresent);

        if ($value !== $valueIfNotPresent) {
            return [
                [$this->field_def->getId(), 'input', $value],
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not', 'contains', 'notcontains'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCapabilities()
    {
        return ['is', 'not', 'empty', 'notempty'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'input';
    }
}
