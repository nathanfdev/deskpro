<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\DataSerializer;

use DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer;
use DeskPRO\Bundle\AppBundle\DataSerializer\Exception\DataSerializerException;
use Orb\Util\Strings;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Hands you a Transformer\AbstractDataSerializerTransformer instance for an object type.
 *
 * This factory is used throughout the DataSerializer package to find the right transformer forn object type.
 */
class DataTransformerFactory
{
    const TRANSFORMER_SERVICE_PREFIX = 'data_serializer.transformer.';

    /**
     * @var ContainerInterface
     */
    private $container;
    /**
     * @var DataPropertyTransformer
     */
    private $property_transformer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ContainerInterface      $container
     * @param DataPropertyTransformer $property_transformer
     * @param LoggerInterface         $logger
     */
    public function __construct(
        ContainerInterface $container,
        DataPropertyTransformer $property_transformer,
        LoggerInterface $logger
    ) {
        // this particular implmentation of a DataTransformerFactory simply uses a service name convention to find
        // a container service.
        // data_serializer.transformer.X
        // where X is the object "type"
        // it's not ideal to use the container directly, but it is contained in this factory service so changing it
        // is relatively straightforward.
        $this->container            = $container;
        $this->property_transformer = $property_transformer;
        $this->logger               = $logger;
    }

    /**
     * @param string $type
     *
     * @return bool
     */
    public function hasType($type)
    {
        $service_name = self::TRANSFORMER_SERVICE_PREFIX.$type;

        if (!$this->container->has($service_name)) {
            $transformer_class = 'DeskPRO\\Bundle\\AppBundle\\DataSerializer\\DataTransformer\\'.ucfirst(Strings::underscoreToCamelCase($type)).'Transformer';
            if (!class_exists($transformer_class)) {
                return false;
            }

            return true;
        } else {
            return true;
        }
    }

    /**
     * @param string $type the object "type" string, like "ticket" or "person"
     *
     * @throws DataSerializerException
     *
     * @return AbstractDataSerializerTransformer
     */
    public function findByType($type)
    {
        $service_name = self::TRANSFORMER_SERVICE_PREFIX.$type;

        $transformer = null;
        if (!$this->container->has($service_name)) {
            // the service does NOT exist, so we are going to try to initialize a Transformer based
            // on convention...
            $transformer_class = 'DeskPRO\\Bundle\\AppBundle\\DataSerializer\\DataTransformer\\'.ucfirst(Strings::underscoreToCamelCase($type)).'Transformer';
            if (!class_exists($transformer_class)) {
                $log_string = 'could not find a data serializer transformer for type: "'.$type.'". Either declare a service with the name "'.$service_name.'" or create the class "'.$transformer_class.'"';
                $this->logger->error($log_string);
                throw new DataSerializerException($log_string);
            }

            $transformer = new $transformer_class();
        } else {
            $transformer = $this->container->get($service_name);
        }

        if (!$transformer instanceof AbstractDataSerializerTransformer) {
            $log_string = 'All DataTransformers must extend "DeskPRO\Bundle\AppBundle\DataSerializer\DataTransformer\AbstractDataSerializerTransformer" but the transformer "'.get_class($transformer).'" does not.';
            $this->logger->error($log_string);
            throw new DataSerializerException($log_string);
        }

        $this->injectSetters($transformer);

        return $transformer;
    }

    /**
     * To make creating transformers easier, we auto-inject these services via setters on
     * the AbstractDataSeralizerTransformer.
     *
     * @param AbstractDataSerializerTransformer $transformer
     */
    protected function injectSetters(AbstractDataSerializerTransformer $transformer)
    {
        $transformer->setPropertyTransformer($this->property_transformer);
        $transformer->setLogger($this->logger);
    }
}
