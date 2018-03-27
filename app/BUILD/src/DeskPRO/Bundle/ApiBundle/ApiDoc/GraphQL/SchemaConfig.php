<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\GraphQL;

/**
 * Stores configuration values related to creating GraphQL schemas.
 */
class SchemaConfig
{
    /**
     * @var string
     */
    protected $basePath = '';

    /**
     * @var int
     */
    protected $basePathLength = 0;

    /**
     * @var array
     */
    protected $ignoredOperations = [];

    /**
     * @var bool
     */
    protected $triggersErrors = false;

    /**
     * Constructor
     *
     * @param string $basePath
     * @param bool $errorsEnabled
     */
    public function __construct($basePath = '', $errorsEnabled = false)
    {
        $this->setBasePath($basePath);
        $this->setTriggersErrors($errorsEnabled);
    }

    /**
     * Returns the API base path
     *
     * @return string
     */
    public function getBasePath()
    {
        return $this->basePath;
    }

    /**
     * Sets the API base path
     *
     * @param string $basePath
     *
     * @return $this
     */
    public function setBasePath($basePath)
    {
        $this->basePath = (string)$basePath;
        $this->basePathLength = strlen($this->basePath);

        return $this;
    }

    /**
     * Removes the configured base path from the beginning of the given path
     *
     * Returns the path unaltered when it does not begin with the base path.
     *
     * @param string $path
     * @return string
     */
    public function trimBasePath($path)
    {
        if (stripos($path, $this->basePath) === 0) {
            $path = substr($path, $this->basePathLength);
        }

        return $path;
    }

    /**
     * Returns a boolean indicating whether notices are enabled
     *
     * @return bool
     */
    public function getTriggersErrors()
    {
        return $this->triggersErrors;
    }

    /**
     * Enabled and disables triggering notices
     *
     * When enabled, E_USER_NOTICE will be triggered when ApiDoc annotations do not contain
     * complete type information. When disabled, any invalid ApiDoc annotations will be
     * skipped, and not included in the generated schema.
     *
     * @param bool $triggersErrors Whether to enable notices
     *
     * @return $this
     */
    public function setTriggersErrors($triggersErrors)
    {
        $this->triggersErrors = $triggersErrors;

        return $this;
    }

    /**
     * Triggers the given error message when notices are enabled
     *
     * @param string $errorMsg
     * @param int $errorType
     */
    public function triggerError($errorMsg, $errorType = E_USER_NOTICE)
    {
        if ($this->triggersErrors) {
            trigger_error($errorMsg, $errorType);
        }
    }

    /**
     * Returns a list of operations which are not included in the generated schema
     *
     * @return array
     */
    public function getIgnoredOperations()
    {
        return $this->ignoredOperations;
    }

    /**
     * Sets a list of operations which are not included in the generated schema
     *
     * @param array $ignoredOperations The operations to ignore
     *
     * @return $this
     */
    public function setIgnoredOperations(array $ignoredOperations)
    {
        $this->ignoredOperations = $ignoredOperations;

        return $this;
    }

    /**
     * Adds an operation which will not be included in the generated schema
     *
     * @param string $operation The operation to ignore
     *
     * @return $this
     */
    public function addIgnoredOperation($operation)
    {
        $this->ignoredOperations[] = (string)$operation;

        return $this;
    }

    /**
     * Returns a boolean indicating whether the given operation has been ignored
     *
     * @param string $operation
     *
     * @return bool
     */
    public function isIgnoredOperation($operation)
    {
        return in_array($operation, $this->ignoredOperations);
    }
}
