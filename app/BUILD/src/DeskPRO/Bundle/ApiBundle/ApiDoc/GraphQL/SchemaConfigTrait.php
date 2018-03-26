<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL;

/**
 * Provides getters/setters for a SchemaConfig instance.
 */
trait SchemaConfigTrait
{
    /**
     * @var SchemaConfig
     */
    protected $config;

    /**
     * Returns the schema configuration
     *
     * @return SchemaConfig
     */
    public function getSchemaConfig()
    {
        return $this->config;
    }

    /**
     * Sets the schema configuration
     *
     * @param SchemaConfig $config
     *
     * @return $this
     */
    public function setSchemaConfig(SchemaConfig $config)
    {
        $this->config = $config;

        return $this;
    }
}
