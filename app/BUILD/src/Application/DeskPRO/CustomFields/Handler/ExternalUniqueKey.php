<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Validator\Constraints as AppAssert;

/**
 * Handles the external id fields.
 */
class ExternalUniqueKey extends HandlerAbstract
{
    /**
     * @param array $form_data
     * @param null  $default
     *
     * @return mixed|null
     */
    private function findValue(array $form_data, $default = null)
    {
        $names = $this->getAllFormFieldNames();
        foreach ($names as $name) {
            if (!empty($form_data[$name]) || (isset($form_data[$name]) && $form_data[$name] === '0')) {
                return $form_data[$name];
            }
        }

        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $value = $this->findValue($formData);
        if (is_array($value)) {
            $value = implode(' ', $value);
        }

        return [
            [$this->field_def['id'], 'input', $value],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $formData, $context = self::CONTEXT_USER, $contextData = null)
    {
        $data = $this->findValue($formData);
        if (!is_scalar($data)) {
            $data = '';
        }

        $customData = null;
        if ($contextData instanceof Person) {
            // we need to find an existing value or create a new one if the one doesn't exist
            $customData = App::$container->getEm()->getRepository($this->field_def->getCustomDataClass())->findOneBy([
                'root_field' => $this->field_def,
                'person'     => $contextData,
            ]);
        }

        if (!$customData) {
            $customData = $this->field_def->createCustomData();
            $customData->setField($this->field_def);
            $customData->setRootField($this->field_def);
            $customData->setPerson($contextData);
        }

        $customData->setValue($data);

        $errors = App::$container->get('validator')->validate($customData, [
            new AppAssert\CustomField\UniqueKey([
                'custom_def' => $this->field_def,
                'context'    => 'agent',
            ]),
        ]);

        if ($errors->count() > 0) {
            $errorCodes = [];
            foreach ($errors as $error) {
                $errorCodes[] = $error->getCode();
            }

            return $this->makeErrorArray($errorCodes);
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
        return ['is', 'not'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'input';
    }
}
