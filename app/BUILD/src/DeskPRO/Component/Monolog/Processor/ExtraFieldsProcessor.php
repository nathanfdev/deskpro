<?php


namespace DeskPRO\Component\Monolog\Processor;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use Monolog\Processor\ProcessorInterface;

/**
 * Class ExtraFieldsProcessor.
 */
class ExtraFieldsProcessor implements ProcessorInterface
{
    /**
     * @var AppEnv
     */
    private $env;

    /**
     * Constructor.
     *
     * @param AppEnv $env
     */
    public function __construct(AppEnv $env)
    {
        $this->env = $env;
    }

    /**
     * {@inheritDoc}
     */
    public function __invoke(array $record)
    {
        $extraData = $this->env->getConfig('settings.extra_errorlog_fields', []);
        $record    = array_merge($record, $extraData);

        return $record;
    }
}
