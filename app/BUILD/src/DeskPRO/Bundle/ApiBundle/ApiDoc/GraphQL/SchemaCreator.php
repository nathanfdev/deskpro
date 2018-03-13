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

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ListOfType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;

/**
 * Creates a GraphQL schema from a list of @ApiDoc annotations.
 */
class SchemaCreator implements SchemaCreatorInterface
{
    use SchemaConfigTrait;

    /**
     * @var TypeRegistry
     */
    protected $registry;

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
     * {@inheritdoc}
     */
    public function createSchema(array $annotations, callable $resolver)
    {
        return new Schema([
            'types'    => $this->createTypes($annotations),
            'query'    => $this->createQuery($annotations, $resolver),
            'mutation' => $this->createMutation($annotations, $resolver)
        ]);
    }

    /**
     * Generates the query information for the given annotations
     *
     * @param ApiDoc[] $annotations
     * @param callable $resolver
     *
     * @return ObjectType
     */
    protected function createQuery(array $annotations, callable $resolver)
    {
        $fields = [];
        foreach($annotations as $annotation) {
            if ($annotation->getMethod() !== 'GET') {
                continue;
            }

            $path = $annotation->getRoute()->getPath();
            $operation = $this->transformPathToTypeName($path);
            if (!$operation) {
                continue;
            }
            $section = $this->formatSection($annotation->getSection());
            $operation = "{$section}_get_{$operation}";

            if (!$this->config->isIgnoredOperation($operation)) {
                $fields[$operation] = $this->buildQueryField($annotation, $resolver, $path);
            }
        }

        return new ObjectType([
            'name'   => 'Query',
            'fields' => $fields,
        ]);
    }

    /**
     * @param ApiDoc $annotation
     * @param callable $resolver
     * @param string $path
     * @return array
     */
    protected function buildQueryField(ApiDoc $annotation, callable $resolver, $path)
    {
        $type = Type::string();
        if ($output = $annotation->getOutput()) {

            $output   = TypeRegistry::formatDataType($output);
            $is_array = TypeRegistry::isArrayType($output);
            if ($is_array) {
                $output = TypeRegistry::getArrayType($output);
            }

            if ($output instanceof Type) {
                $type = $output;
            } else if ($this->registry->hasObjectType($output)) {
                $type = $this->registry->getObjectType($output);
            } else {
                $this->config->triggerError(
                    sprintf('Output type "%s" not found in registry.', $type)
                );
                $type = Type::string();
            }
            if ($is_array && !($type instanceof ListOfType)) {
                $type = Type::listOf($type);
            }
        }

        $args = [];
        foreach($this->extractPathArgs($path) as $arg) {
            if ($arg === 'id' || $arg === 'parentId') {
                $args[$arg] = Type::nonNull(Type::id());
            } else {
                $args[$arg] = Type::nonNull(Type::string());
            }
        }

        // @see https://webonyx.github.io/graphql-php/type-system/object-types/
        $resolve = function($root, $args, $request, ResolveInfo $info) use($resolver, $annotation) {
            return call_user_func($resolver, $request, $info, $annotation, $args, $root);
        };

        return [
            'type'        => Type::nonNull($type),
            'args'        => $args,
            'resolve'     => $resolve,
            'description' => $annotation->getDescription()
        ];
    }

    /**
     * Generates the mutation information for the given annotations
     *
     * @param ApiDoc[] $annotations
     * @param callable $resolver
     *
     * @return ObjectType
     */
    protected function createMutation(array $annotations, callable $resolver)
    {
        $fields = [];
        foreach($annotations as $annotation) {
            $method = $annotation->getMethod();
            if ($method === 'GET') {
                continue;
            }

            $operation = $this->transformPathToTypeName($annotation->getRoute()->getPath());
            if (!$operation) {
                continue;
            }
            switch($method) {
                case 'POST':
                    $operation = "set_{$operation}";
                    break;
                case 'PUT':
                    $operation = "update_{$operation}";
                    break;
                case 'DELETE':
                    $operation = "delete_{$operation}";
                    break;
            }
            $section   = $this->formatSection($annotation->getSection());
            $operation = "{$section}_{$operation}";

            if (!$this->config->isIgnoredOperation($operation)) {
                $fields[$operation] = $this->buildMutationField($annotation, $resolver);
            }
        }

        return new ObjectType([
            'name'   => 'Mutation',
            'fields' => $fields,
        ]);
    }

    /**
     * @param ApiDoc $annotation
     * @param callable $resolver
     * @return array
     */
    protected function buildMutationField(ApiDoc $annotation, callable $resolver)
    {
        $args = [];
        $data = array_merge([
            'requirements' => [],
            'parameters'   => []
        ], $annotation->toArray());
        foreach($data['requirements'] as $name => $fields) {
            if ($arg = $this->buildMutationArg($name, $fields)) {
                $args[$name] = $arg;
            }
        }
        foreach($data['parameters'] as $name => $fields) {
            if ($arg = $this->buildMutationArg($name, $fields)) {
                $args[$name] = $arg;
            }
        }

        $type = Type::boolean();
        if ($returnType = $this->cleanType($annotation->getOutput())) {
            $type = $this->registry->getObjectType($returnType);
        }

        // @see https://webonyx.github.io/graphql-php/type-system/object-types/
        $resolve = function($root, $args, $request, ResolveInfo $info) use($resolver, $annotation) {
            return call_user_func($resolver, $request, $info, $annotation, $args, $root);
        };

        return [
            'type'        => $type,
            'args'        => $args,
            'resolve'     => $resolve,
            'description' => $annotation->getDescription()
        ];
    }

    /**
     * @param string $name
     * @param array $fields
     *
     * @return InputObjectType
     */
    protected function buildMutationArg($name, array $fields)
    {
        if (empty($fields['dataType'])) {
            $fields['dataType'] = 'integer';
        }

        $arg = TypeRegistry::formatDataType($fields['dataType']);
        if (TypeRegistry::isArrayType($arg)) {
            $arg = TypeRegistry::getArrayType($arg);
        }

        if (!($arg instanceof Type)) {
            $arg = TypeRegistry::addInputSuffix($arg);
            if (!$this->registry->hasInputObjectType($arg) && isset($fields['children'])) {
                $this->registry->registerInputObjectType($arg, $fields['children']);
            }
            if ($this->registry->hasInputObjectType($arg)) {
                $arg = $this->registry->getInputObjectType($arg);
            } else {
                $this->config->triggerError(
                    sprintf('Could not build mutation arg "%s".', $name)
                );
                $arg = null;
            }
        }

        return $arg;
    }

    /**
     * Generates the type information for the given annotations
     *
     * @param ApiDoc[] $annotations
     *
     * @return array
     */
    protected function createTypes(array $annotations)
    {
        $this->registry = new TypeRegistry($this->getSchemaConfig());
        foreach($annotations as $annotation) {
            $this->registerObjectType($annotation);
            $this->registerInputObjectType($annotation);
        }

        return $this->registry->toArray();
    }

    /**
     * Extracts type information from the given annotation, and adds it to the type registry
     *
     * @param ApiDoc $annotation
     */
    protected function registerObjectType(ApiDoc $annotation)
    {
        $type = $this->cleanType($annotation->getOutput());
        if (!$type || $this->registry->hasObjectType($type)) {
            return;
        }

        $data = $annotation->toArray();
        if (!isset($data['response'])) {
            $this->config->triggerError(
                sprintf('Response type not found for type "%s".', $type)
            );

            return;
        }

        if (key($data['response']) === '') {
            $children = array_pop($data['response']);
            $children = $children['children'];
        } else {
            $children = $data['response'];
        }
        $this->registry->registerObjectType($type, $children);
    }

    /**
     * Extracts input type information from the given annotation, and adds it to the type registry
     *
     * @param ApiDoc $annotation
     */
    protected function registerInputObjectType(ApiDoc $annotation)
    {
        $type = $this->cleanType($annotation->getInput());
        $type = TypeRegistry::addInputSuffix($type);
        if (!$type || $this->registry->hasInputObjectType($type)) {
            return;
        }

        $data = $annotation->toArray();
        if (!isset($data['parameters'])) {
            $this->config->triggerError(
                sprintf('Parameters not found for type "%s".', $type)
            );

            return;
        }

        if (count($data['parameters']) !== 1) {
            $fields = $data['parameters'];
        } else {
            $fields = array_pop($data['parameters']);
            $fields = $fields['children'];
        }
        $this->registry->registerInputObjectType($type, $fields);
    }

    /**
     * Converts a REST endpoint path to a GraphQL type name
     *
     * @param string $path
     *
     * @return null|string
     */
    protected function transformPathToTypeName($path)
    {
        $path = $this->config->trimBasePath($path);
        if ($this->config->isIgnoredOperation($path)) {
            return null;
        }

        $path = preg_replace('/{([\w]+)}/', '', $path);
        $path = preg_replace('/[^\w]/', '_', $path);
        $path = preg_replace('/[_]{2,}/', '_', $path);
        $path = trim($path, '_');

        return $path;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function extractPathArgs($path)
    {
        $args = [];
        if (preg_match_all('/{([\w]+)}/', $path, $matches)) {
            for($i = 0, $c = count($matches[1]); $i < $c; $i++) {
                $args[] = $matches[1][$i];
            }
        }

        return $args;
    }

    /**
     * @param string $section
     *
     * @return string
     */
    protected function formatSection($section)
    {
        $section = preg_replace('/[^\w]/', ' ', $section);
        $section = strtolower($section);
        $section = preg_replace('/\s/', '_', $section);

        return $section;
    }

    /**
     * @param string $type
     *
     * @return null|string
     */
    protected function cleanType($type)
    {
        if (!$type) {
            return null;
        }
        $type = TypeRegistry::formatDataType($type);
        if (is_object($type)) {
            return null;
        }
        if (TypeRegistry::isArrayType($type)) {
            $type = TypeRegistry::getArrayType($type);
        }

        return $type;
    }
}
