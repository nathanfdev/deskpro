<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Parser;

/**
 * Class FormTypeParser.
 */
class FormTypeParser extends \Nelmio\ApiDocBundle\Parser\FormTypeParser
{
    /**
     * {@inheritdoc}
     */
    public function supports(array $item)
    {
        return parent::supports($this->prepareOptions($item));
    }

    /**
     * {@inheritdoc}
     */
    public function parse(array $item)
    {
        $parameters = parent::parse($this->prepareOptions($item));
        if ($parameters && $this->isPutMethod($item)) {
            foreach ($parameters as $name => $value) {
                if (is_array($value) && array_key_exists('children', $value)) {
                    $parameters[$name]['children'] = $this->setNotRequired($value['children']);
                }
            }
        }

        return $parameters;
    }

    /**
     * @param array $item
     *
     * @return array
     */
    private function prepareOptions(array $item)
    {
        if (!empty($item['options'])) {
            foreach ($item['options'] as &$option) {
                if (is_string($option) && class_exists($option)) {
                    $reflection = new \ReflectionClass($option);
                    $option     = $reflection->newInstance();
                }
            }
        }

        return $item;
    }

    /**
     * @param array $item
     *
     * @return bool
     */
    private function isPutMethod(array $item)
    {
        return !empty($item['options'])
            && array_key_exists('method', $item['options'])
            && strtolower($item['options']['method']) === 'put';
    }

    /**
     * @param array $children
     *
     * @return array
     */
    private function setNotRequired(array $children)
    {
        foreach ($children as $name => $properties) {
            $children[$name]['required'] = false;
            if (is_array($properties) && array_key_exists('children', $properties)) {
                $children[$name]['children'] = $this->setNotRequired($properties['children']);
            }
        }

        return $children;
    }
}
