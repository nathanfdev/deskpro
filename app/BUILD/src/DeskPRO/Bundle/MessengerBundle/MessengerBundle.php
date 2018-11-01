<?php

namespace DeskPRO\Bundle\MessengerBundle;

use DeskPRO\Bundle\AppBundle\Form\Error\ErrorsCodes;
use DeskPRO\Bundle\AppBundle\Form\Error\ExceptionErrorCodeFactory;
use DeskPRO\Bundle\MessengerBundle\Exception\MessengerApiException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class MessengerBundle.
 */
class MessengerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        ExceptionErrorCodeFactory::$exceptions_to_error_codes_map[MessengerApiException::class] = ErrorsCodes::BAD_REQUEST;
    }

    /**
     * {@inheritdoc}
     */
    public function getNamespace()
    {
        return __NAMESPACE__;
    }

    /**
     * {@inheritdoc}
     */
    public function getPath()
    {
        return __DIR__;
    }
}
