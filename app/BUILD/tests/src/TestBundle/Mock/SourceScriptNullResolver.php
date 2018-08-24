<?php

namespace DpTestSrc\TestBundle\Mock;

use DeskPRO\Bundle\ImportBundle\Source\SourceScriptResolver;
use DeskPRO\ImporterTools\ImporterInterface;

/**
 * Class SourceScriptNullResolver.
 */
class SourceScriptNullResolver extends SourceScriptResolver
{
    /**
     * {@inheritdoc}
     */
    public function getSourceScript($filename, array $config)
    {
        return new NullScript();
    }
}

/**
 * Class NullScript.
 */
class NullScript implements ImporterInterface
{
    /**
     * {@inheritdoc}
     */
    public function init(array $config)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function testConfig()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function runImport()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function addHelper($helper)
    {
    }
}
