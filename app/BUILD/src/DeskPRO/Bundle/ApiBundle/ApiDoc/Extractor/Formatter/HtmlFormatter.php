<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\Formatter;

use Nelmio\ApiDocBundle\DataTypes;
use Nelmio\ApiDocBundle\Formatter\HtmlFormatter as BaseHtmlFormatter;

/**
 * Class HtmlFormatter.
 */
class HtmlFormatter extends BaseHtmlFormatter
{
    /**
     * {@inheritdoc}
     */
    protected function processAnnotation($annotation)
    {
        if (isset($annotation['parameters'])) {
            $annotation['parameters'] = $this->compressNestedParameters($annotation['parameters'], null, true);
        }

        if (isset($annotation['response'])) {
            $annotation['response'] = $this->compressResponse($annotation['response'], true);
        }

        if (isset($annotation['parsedResponseMap'])) {
            foreach ($annotation['parsedResponseMap'] as $statusCode => &$data) {
                $data['model'] = $this->compressNestedParameters($data['model']);
            }
        }

        $annotation['id'] = strtolower($annotation['method']).'-'.str_replace('/', '-', $annotation['uri']);

        return $annotation;
    }

    /**
     * @param array $data
     * @param bool  $firstTime
     * @param null  $parentName
     * @param bool  $ignoreNestedReadOnly
     *
     * @return array
     */
    protected function compressResponse($data, $firstTime, $parentName = null, $ignoreNestedReadOnly = false)
    {
        $newParams = [];
        $root      = count($data) == 1 && $firstTime; //income is just 1 element, with $firstTime it means this is root
        foreach ($data as $name => $info) {
            $newName = $this->getNewName($name, $info, $parentName);
            $prefix  = '';
            if ($root && ($info['actualType'] === DataTypes::COLLECTION)) {
                $prefix = 'data';
            } elseif ($firstTime) {
                $prefix = 'data.';
            }

            $newParams[$prefix.$newName] = [
                'dataType'     => $info['dataType'],
                'readonly'     => array_key_exists('readonly', $info) ? $info['readonly'] : null,
                'required'     => $info['required'],
                'default'      => array_key_exists('default', $info) ? $info['default'] : null,
                'description'  => array_key_exists('description', $info) ? $info['description'] : null,
                'format'       => array_key_exists('format', $info) ? $info['format'] : null,
                'sinceVersion' => array_key_exists('sinceVersion', $info) ? $info['sinceVersion'] : null,
                'untilVersion' => array_key_exists('untilVersion', $info) ? $info['untilVersion'] : null,
                'actualType'   => array_key_exists('actualType', $info) ? $info['actualType'] : null,
                'subType'      => array_key_exists('subType', $info) ? $info['subType'] : null,
            ];

            if (isset($info['children']) && (!$info['readonly'] || !$ignoreNestedReadOnly)) {
                foreach ($this->compressResponse($info['children'], false, $newName, $ignoreNestedReadOnly) as $nestedItemName => $nestedItemData) {
                    $newParams[$prefix.$nestedItemName] = $nestedItemData;
                }
            }
        }

        return $newParams;
    }

    /**
     * {@inheritdoc}
     */
    protected function compressNestedParameters(array $data, $parentName = null, $ignoreNestedReadOnly = false, $root = true)
    {
        $newParams = [];
        foreach ($data as $name => $info) {
            $newName = $root ? '' : $this->getNewName($name, $info, $parentName);

            if (isset($info['children']) && (!$info['readonly'] || !$ignoreNestedReadOnly)) {
                foreach ($this->compressNestedParameters($info['children'], $newName, $ignoreNestedReadOnly, false) as $nestedItemName => $nestedItemData) {
                    $newParams[$nestedItemName] = $nestedItemData;
                }
            } else {
                $newParams[$newName] = [
                    'dataType'     => $info['dataType'],
                    'readonly'     => array_key_exists('readonly', $info) ? $info['readonly'] : null,
                    'required'     => $info['required'],
                    'default'      => array_key_exists('default', $info) ? $info['default'] : null,
                    'description'  => array_key_exists('description', $info) ? $info['description'] : null,
                    'format'       => array_key_exists('format', $info) ? $info['format'] : null,
                    'sinceVersion' => array_key_exists('sinceVersion', $info) ? $info['sinceVersion'] : null,
                    'untilVersion' => array_key_exists('untilVersion', $info) ? $info['untilVersion'] : null,
                    'actualType'   => array_key_exists('actualType', $info) ? $info['actualType'] : null,
                    'subType'      => array_key_exists('subType', $info) ? $info['subType'] : null,
                ];
            }
        }

        return $newParams;
    }
}
