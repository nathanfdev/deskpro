<?php

namespace DeskPRO\Bundle\AuditBundle\Configuration;

use DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy\DefaultNamingStrategy;
use DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy\ExpressionNamingStrategy;
use DeskPRO\Bundle\AuditBundle\Entity\NamingStrategy\NamingStrategyInterface;
use DeskPRO\Bundle\AuditBundle\EventListener\AuditListener;
use DeskPRO\Bundle\AuditBundle\Log\FieldFilter\FieldFilterService;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

/**
 * Class ConfigurationBuilder.
 */
class ConfigurationBuilder
{
    /**
     * @var ConfigurationSet
     */
    private $configurationSet;

    /**
     * @var FieldFilterService
     */
    private $fieldFilterService;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * ConfigurationBuilder constructor.
     *
     * @param FieldFilterService $fieldFilterService
     * @param ContainerInterface $container
     */
    public function __construct(FieldFilterService $fieldFilterService, ContainerInterface $container)
    {
        $this->fieldFilterService = $fieldFilterService;
        $this->container          = $container;
        $this->language           = new ExpressionLanguage();
    }

    /**
     * @param bool  $rebuild
     * @param array $rawConfig
     *
     * @return ConfigurationSet
     */
    public function buildConfigurationSet(array $rawConfig, $rebuild = false)
    {
        if (!$this->configurationSet || $rebuild === true) {
            $this->configurationSet = new ConfigurationSet();
            foreach ($rawConfig as $entityClass => $actions) {
                if (isset($actions[AuditListener::ALL])) {
                    $all = $actions[AuditListener::ALL];
                    unset($actions[AuditListener::ALL]);
                } else {
                    $all = [];
                }
                foreach ($actions as $action => $configEntry) {
                    if ($configEntry) {
                        $configEntry = is_array($configEntry) ? $configEntry : [];
                        $configEntry = array_replace_recursive($all, $configEntry);
                        $this->configurationSet->registerConfiguration(
                            $this->buildConfiguration(
                                $entityClass,
                                $action,
                                $configEntry
                            )
                        );
                    }
                }
            }
        }

        return $this->configurationSet;
    }

    /**
     * @param $entityClass
     * @param $action
     * @param $configEntry
     *
     * @return Configuration
     */
    public function buildConfiguration($entityClass, $action, $configEntry)
    {
        $configuration = new Configuration($entityClass, $action);
        $this->processConditions($configuration, $configEntry);
        $this->processFields($configuration, $configEntry);
        $this->processFieldFilters($configuration, $action, $configEntry);
        $this->processNamingStrategy($configuration, $configEntry);

        return $configuration;
    }

    /**
     * @param Configuration $configuration
     * @param array         $configEntry
     */
    private function processConditions(Configuration $configuration, array $configEntry)
    {
        if (isset($configEntry['conditions']) && is_array($configEntry['conditions'])) {
            foreach ($configEntry['conditions'] as $condition) {
                $preconditions = isset($condition['preconditions']) ? $condition['preconditions'] : [];
                if (!isset($condition['expression']) || !$condition['expression']) {
                    $conditionObject = new Condition($preconditions); // this will be executed much more frequently
                } else {
                    $conditionObject = new Condition(
                        $preconditions,
                        isset($condition['expression']) ? $condition['expression'] : '',
                        isset($condition['variables']) ? $condition['variables'] : [],
                        $this->language
                    );
                }
                $configuration->addCondition($conditionObject);
            }
        }
    }

    /**
     * @param Configuration $configuration
     * @param array         $configEntry
     */
    private function processFields(Configuration $configuration, array $configEntry)
    {
        if (isset($configEntry['fields']) && is_array($configEntry['fields'])) {
            foreach ($configEntry['fields'] as $field) {
                $configuration->addField($field);
            }
        }
    }

    /**
     * @param Configuration $configuration
     * @param string        $action
     * @param array         $configEntry
     */
    private function processFieldFilters(Configuration $configuration, $action, array $configEntry)
    {
        if (isset($configEntry['field_filters']) && is_array($configEntry['field_filters'])) {
            foreach ($configEntry['field_filters'] as $field => $fieldsFilters) {
                $this->fieldFilterService->buildFilters(
                    $fieldsFilters,
                    $configuration->getEntityClass(),
                    $field,
                    $action
                );
            }
        }
    }

    private function processNamingStrategy(Configuration $configuration, array $configEntry)
    {
        $namingStrategy = null;
        if (isset($configEntry['naming']) && isset($configEntry['naming']['type'])) {
            switch ($configEntry['naming']['type']) {
                case 'expression':
                    $namingStrategy = new ExpressionNamingStrategy($configEntry['naming']['expression']);
                    break;
                case 'service':
                    $namingStrategy = $this->container->get($configEntry['naming']['id']);
                    if (!$namingStrategy instanceof NamingStrategyInterface) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Service [ %s ] should implement NamingStrategyInterface',
                                $configEntry['naming']['id']
                            )
                        );
                    }
                    break;
            }
        }

        if (!$namingStrategy) {
            $namingStrategy = new DefaultNamingStrategy();
        }

        $configuration->setNamingStrategy($namingStrategy);
    }
}
