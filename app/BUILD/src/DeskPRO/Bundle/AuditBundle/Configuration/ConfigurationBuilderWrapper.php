<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use Application\DeskPRO\NewSettings\SettingsResolver;

class ConfigurationBuilderWrapper
{
    private $resolver;

    public function __construct(SettingsResolver $resolver, ConfigurationBuilder $builder)
    {
        $this->resolver = $resolver;
        $this->builder  = $builder;
    }

    public function buildConfigurationSet($rebuild = false)
    {
        $rawConfig = $this->resolver->getGlobalSettings($rebuild)->get('audit_log.configuration');

        return $this->builder->buildConfigurationSet($rawConfig, $rebuild);
    }
}
