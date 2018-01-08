<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use RuntimeException;

/**
 * Class TypeRegistry
 */
class TypeRegistry
{
    use SchemaConfigTrait;

    /**
     * Added to the end of input type names
     */
    const INPUT_SUFFIX = 'Input';

    /**
     * @var array
     */
    protected $objectFields = [];

    /**
     * @var array
     */
    protected $inputFields = [];

    /**
     * @var array
     */
    protected $objectTypes = [];

    /**
     * @var array
     */
    protected $inputTypes = [];

    /**
     * @var array
     */
    protected $objectStack = [];

    /**
     * @var array
     */
    protected $inputStack = [];

    /**
     * Constructor
     *
     * @param SchemaConfig $config
     */
    public function __construct(SchemaConfig $config = null)
    {
        $this->setSchemaConfig($config ?: new SchemaConfig());
    }

    /**
     * Returns the definition for the given type
     *
     * @param string $inputType The name of the type
     *
     * @return InputObjectType|null
     *
     * @throws RuntimeException
     */
    public function getInputObjectType($inputType)
    {
        if (!isset($this->inputTypes[$inputType]) && !isset($this->inputFields[$inputType])) {
            throw new RuntimeException(sprintf('Input type "%s" not found.', $inputType));
        }

        if (!isset($this->inputTypes[$inputType])) {
            array_push($this->inputStack, $inputType);
            $this->buildInputObjectType($inputType);
            array_pop($this->inputStack);
        }

        return $this->inputTypes[$inputType];
    }

    /**
     * Returns the swagger data type for the given type
     *
     * @param string $dataType
     *
     * @return string
     */
    public static function formatDataType($dataType)
    {
        $dataType = self::cleanDataType($dataType);

        if (preg_match('/^objects?\s+\(([\w]+)\)$/', $dataType, $matches)) {
            $dataType = self::formatClassName($matches[1]);
        } else if (preg_match('/^array of objects (.*)$/', $dataType, $matches)) {
            $dataType = [self::formatClassName($matches[1])];
        } else if (preg_match('/^array of integer ids/', $dataType)) {
            $dataType = Type::listOf(Type::id());
        } else if (preg_match('/^array of (.*)$/', $dataType, $matches)) {
            $dataType = [self::formatClassName($matches[1])];
        } else if (preg_match('/^array<([\w\\\]+)>$/', $dataType, $matches)) {
            if ($matches[1] === 'string') {
                $dataType = Type::listOf(Type::string()); // edge case!
            } else if ($matches[1] === 'integer') {
                $dataType = Type::listOf(Type::int()); // edge case!
            } else {
                $dataType = [self::formatClassName($matches[1])];
            }
        } else if (preg_match('/^integer id/', $dataType)) {
            $dataType = Type::id();
        } else if (preg_match('/^integer/', $dataType)) {
            $dataType = Type::int();
        } else if (preg_match('/^string|custom|token/i', $dataType)) {
            $dataType = Type::string();
        } else if ($dataType === 'choice' || $dataType === 'DateTime') {
            $dataType = Type::string();
        } else if ($dataType === 'boolean') {
            $dataType = Type::boolean();
        } else if ($dataType === 'dynamically declared custom fields (array)') {
            $dataType = Type::listOf(Type::string());
        } else if ($dataType === 'array') {
            $dataType = Type::listOf(Type::string());
        } else {
            $dataType = self::formatClassName($dataType);
        }

        return $dataType;
    }

    /**
     * @param string $type
     *
     * @return \GraphQL\Type\Definition\Type
     */
    public static function formatFieldType($type)
    {
        if (!isset($type['actualType'])) {
            $type['actualType'] = $type['dataType'];
        }

        switch(self::cleanDataType($type['actualType'])) {
            case 'string':
            case 'datetime':
            case 'choice':
                return Type::string();
                break;
            case 'integer':
                return Type::int();
                break;
            case 'float':
                return Type::float();
                break;
            case 'boolean':
                return Type::boolean();
                break;
            case 'collection':
                return Type::listOf(Type::string()); // @todo fix me!
                break;
        }

        throw new RuntimeException(
            sprintf('Failed to parse field type "%s".', $type['actualType'])
        );
    }

    /**
     * @param string $className
     *
     * @return string
     */
    public static function formatClassName($className)
    {
        $parts      = explode('\\', $className);
        $className = array_pop($parts);

        return preg_replace('/[^\w]/', '', $className);
    }

    /**
     * @param string $dataType
     *
     * @return array
     */
    public static function cleanDataType($dataType)
    {
        if (is_array($dataType) && isset($dataType['class'])) {
            $dataType = $dataType['class'];
        }
        if (strpos($dataType, '|') !== false) {
            $dataType = explode('|', $dataType);
            $dataType = $dataType[0];
        }
        if (empty($dataType)) {
            return Type::string();
        }

        return $dataType;
    }

    /**
     * @param string $type
     * @return string
     */
    public static function addInputSuffix($type)
    {
        if (!$type) {
            return '';
        }
        return $type . self::INPUT_SUFFIX;
    }

    /**
     * @param array|string $type
     *
     * @return bool
     */
    public static function isArrayType($type)
    {
        return is_array($type);
    }

    /**
     * @param array|string $type
     *
     * @return mixed
     */
    public static function getArrayType($type)
    {
        return $type[0];
    }

    /**
     * @param string $typeName
     * @param array $fields
     *
     * @return $this
     */
    public function registerObjectType($typeName, array $fields)
    {
        $this->objectFields[$typeName] = $fields;

        return $this;
    }

    /**
     * @param string $typeName
     * @param array $fields
     *
     * @return $this
     */
    public function registerInputObjectType($typeName, array $fields)
    {
        $this->inputFields[$typeName] = $fields;

        return $this;
    }

    /**
     * Returns whether the registry contains a definition for the given type
     *
     * @param string $objectType
     *
     * @return bool
     */
    public function hasObjectType($objectType)
    {
        return isset($this->objectFields[$objectType]);
    }

    /**
     * Returns whether the registry contains a definition for the given input type
     *
     * @param string $inputType
     *
     * @return bool
     */
    public function hasInputObjectType($inputType)
    {
        return isset($this->inputFields[$inputType]);
    }

    /**
     * Returns the definition for the given type
     *
     * @param string $objectType The name of the type
     *
     * @return ObjectType|null
     *
     * @throws RuntimeException
     */
    public function getObjectType($objectType)
    {
        if (!isset($this->objectTypes[$objectType]) && !isset($this->objectFields[$objectType])) {
            throw new RuntimeException(sprintf('Object type "%s" not found.', $objectType));
        }

        if (!isset($this->objectTypes[$objectType])) {
            array_push($this->objectStack, $objectType);
            $this->buildObjectType($objectType);
            array_pop($this->objectStack);
        }

        return $this->objectTypes[$objectType];
    }

    /**
     * Returns all registered type definitions as an array
     *
     * @return array
     */
    public function toArray()
    {
        $types = [];
        foreach($this->objectFields as $name => $data) {
            $types[$name] = $this->getObjectType($name);
        }
        foreach($this->inputFields as $name => $data) {
            $types[$name] = $this->getInputObjectType($name);
        }

        return $types;
    }

    /**
     * @param string $typeName The name of the type
     */
    protected function buildObjectType($typeName)
    {
        if (isset($this->objectTypes[$typeName])) {
            return;
        }

        $formatted = [];
        foreach($this->objectFields[$typeName] as $fname => $fdata) {
            if (is_numeric($fname)) {
                $this->config->triggerError(
                    sprintf('Object with invalid field name in type %s.', $typeName)
                );
                $fname = 'arg';
            }

            if (!is_array($fdata)) {
                $ftype = self::formatDataType($fdata);
            } else {
                $ftype = self::formatDataType($fdata['dataType']);
            }

            $isArray = self::isArrayType($ftype);
            if ($isArray) {
                $ftype = self::getArrayType($ftype);
            }

            if (is_string($ftype) && !isset($this->objectFields[$ftype])) {
                if (!isset($fdata['children'])) {
                    $ftype = self::formatFieldType($fdata);
                } else {
                    $this->registerObjectType($ftype, $fdata['children']);
                }
            }

            if (!($ftype instanceof Type)) {
                if (!in_array($ftype, $this->objectStack)) {
                    $ftype = $this->getObjectType($ftype);
                } else {
                    $ftype = null;
                }
            }
            if ($ftype !== null) {
                $formatted[$fname] = $ftype;
            }
        }

        if (empty($formatted)) {
            $formatted['id'] = Type::id(); // edge case!
        }

        $this->objectTypes[$typeName] = new ObjectType([
            'name'   => $typeName,
            'fields' => $formatted
        ]);
    }

    /**
     * @param string $typeName The name of the type
     */
    protected function buildInputObjectType($typeName)
    {
        if (isset($this->inputTypes[$typeName])) {
            return;
        }

        $formatted = [];
        foreach($this->inputFields[$typeName] as $fname => $fdata) {
            if (is_numeric($fname)) {
                $this->config->triggerError(
                    sprintf('Input object with invalid field name in type %s.', $typeName)
                );
                $fname = 'arg';
            }

            if (!is_array($fdata)) {
                $ftype = self::formatDataType($fdata);
            } else {
                $ftype = self::formatDataType($fdata['dataType']);
            }

            $isArray = self::isArrayType($ftype);
            if ($isArray) {
                $ftype = self::getArrayType($ftype);
            }
            if (substr($ftype, -5) !== self::INPUT_SUFFIX) {
                $ftype = self::addInputSuffix($ftype);
            }

            if (is_string($ftype) && !isset($this->inputFields[$ftype])) {
                if (!isset($fdata['children'])) {
                    $ftype = self::formatFieldType($fdata);
                } else {
                    $this->registerInputObjectType($ftype, $fdata['children']);
                }
            }

            if (!($ftype instanceof Type)) {
                if (!in_array($ftype, $this->inputStack)) {
                    $ftype = $this->getInputObjectType($ftype);
                } else {
                    $ftype = Type::id();
                }
            }

            $formatted[$fname] = $ftype;
        }

        if (empty($formatted)) {
            $formatted['id'] = Type::id(); // edge case!
        }

        $this->inputTypes[$typeName] = new InputObjectType([
            'name'   => $typeName,
            'fields' => $formatted
        ]);
    }
}
