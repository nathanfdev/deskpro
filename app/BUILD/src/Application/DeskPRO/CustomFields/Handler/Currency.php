<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Application\DeskPRO\App;
use DeskPRO\Bundle\AppBundle\Entity\Currency as CurrencyEntity;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

/**
 * Class Currency.
 */
class Currency extends HandlerAbstract
{
    /**
     * {@inheritdoc}
     */
    public function getWidgetName()
    {
        return 'money';
    }

    /**
     * {@inheritdoc}
     */
    public function getWidgetOptions()
    {
        $em         = App::$container->getEm();
        $currencyId = $this->field_def->getOption('currency_id');
        if (!$currencyId) {
            return [];
        }

        $currency = $em->getRepository(CurrencyEntity::class)->find($currencyId);
        if (!$currency) {
            return [];
        }

        return [
            'currency' => $currency->getCurrencyCode(),
            'divisor'  => $currency->getDelimiter(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not', 'lte', 'gte', 'between'];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterCapabilities()
    {
        return ['is', 'not', 'lte', 'gte', 'between'];
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchType()
    {
        return 'value';
    }

    /**
     * {@inheritdoc}
     */
    public function getDataFromForm(array $formData)
    {
        $value = $this->findValue($formData);
        $form  = $this->getCurrencyForm();

        if ($form) {
            $form->submit($value);
            $value = $form->getData();
        } else {
            $value = 0;
        }

        return [
            [$this->field_def['id'], 'value', $value],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validateFormData(array $formData, $context = self::CONTEXT_USER, $contextData = null)
    {
        $data = $this->findValue($formData);
        if ($data === null) {
            $data = '';
        }

        if (!is_scalar($data)) {
            return $this->makeErrorArray(['invalid_input']);
        }

        $optPrefix = '';
        if ($context == self::CONTEXT_AGENT) {
            $optPrefix = 'agent_';
        }

        $options = [];
        foreach (['required'] as $k) {
            $options[$k] = $this->field_def->getOption($optPrefix.$k);
        }

        if ($options['required']) {
            if ($data === '' || $data === null) {
                return $this->makeErrorArray(['required']);
            }
        }

        $form = $this->getCurrencyForm();
        if (!$form) {
            return $this->makeErrorArray(['invalid_input']);
        }

        $form->submit($data);
        if (!$form->isValid()) {
            return $this->makeErrorArray(['invalid_input']);
        }

        return [];
    }

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
            if (!empty($formData[$name]) || (isset($formData[$name]) && (string) $formData[$name] === '0')) {
                return $formData[$name];
            }
        }

        return $default;
    }

    /**
     * @param mixed $value
     *
     * @return \Symfony\Component\Form\FormInterface|null
     */
    private function getCurrencyForm($value = null)
    {
        return App::$container->getFormFactory()->createBuilder(MoneyType::class, $value, $this->getWidgetOptions())->getForm();
    }
}
