<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use GraphQL\Type\Schema;

/**
 * Creates a GraphQL schema from a list of @ApiDoc annotations.
 */
interface SchemaCreatorInterface
{
    /**
     * Generates a schema for the given annotations
     *
     * @param ApiDoc[] $annotations
     * @param callable $resolver
     *
     * @return Schema
     */
    public function createSchema(array $annotations, callable $resolver);
}
