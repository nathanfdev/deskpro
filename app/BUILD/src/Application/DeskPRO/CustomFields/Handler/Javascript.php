<?php

namespace Application\DeskPRO\CustomFields\Handler;

use Orb\Util\Util;

/**
 * Handles the text field.
 */
class Javascript extends HandlerAbstract
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
                if (json_last_error() !== JSON_ERROR_NONE) {
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
     * Render the field to HTML for use in a web page.
     */
    public function renderHtml($data = null, array $template_vars = [])
    {
        if ($data === null) {
            $data = [];
        }

        $templating = $this->getTemplateEngine();

        $vars = array_merge($this->getRenderTemplateVars('html'), $template_vars, [
            'data'               => $data,
            'field_def'          => $this->field_def,
            'field_handler'      => $this,
            'field_handler_name' => Util::getBaseClassname($this),
            'field_type'         => $this->field_def->getTableName(),
        ]);

        return $templating->render($this->getRenderTemplateName('html'), $vars);
    }

    public function getRenderTemplateName($context = 'html')
    {
        $tpl = 'DeskPRO:custom_fields:rendered-javascript-value';
        if ($context == 'html') {
            $tpl .= '.html.twig';
        } else {
            $tpl .= '.txt.twig';
        }

        return $tpl;
    }

    public function getFormTemplateName()
    {
        return 'DeskPRO:custom_fields:form-javascript-input.html.twig';
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCapabilities()
    {
        return ['is', 'not'];
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
