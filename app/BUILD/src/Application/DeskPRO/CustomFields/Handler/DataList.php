<?php

namespace Application\DeskPRO\CustomFields\Handler;

/**
 * Handles the data json value custom field.
 */
class DataList extends HandlerAbstract
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
            $decodedValue = null;

            try {
                $decodedValue = is_string($value) ? json_decode($value) : null;
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decodedValue)) {
                    $decodedValue = null;
                }
            } catch (\Exception $e) {
                $decodedValue = null;
            }

            $data = is_null($decodedValue) ? $value : json_encode($decodedValue);

            return [[$this->field_def->getId(), 'input', $data]];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not', 'empty', 'notempty'];
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
