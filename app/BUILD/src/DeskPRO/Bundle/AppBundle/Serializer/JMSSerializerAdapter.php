<?php

namespace DeskPRO\Bundle\AppBundle\Serializer;

use DeskPRO\Bundle\AppBundle\Serializer\Annotation\SerializerView;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class JMSSerializerAdapter.
 */
class JMSSerializerAdapter implements Serializer
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param SerializerInterface $serializer
     * @param ContainerInterface  $container
     */
    public function __construct(SerializerInterface $serializer, ContainerInterface $container)
    {
        $this->serializer = $serializer;
        $this->container  = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, Context $context)
    {
        return $this->serializer->serialize($data, $format, $this->getSerializationContext($context));
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize($data, $type, $format, Context $context)
    {
        return $this->serializer->deserialize($data, $type, $format, $this->getSerializationContext($context));
    }

    /**
     * @param Context $context
     *
     * @return SideloadSerializationContext
     */
    protected function getSerializationContext(Context $context)
    {
        $jmsContext = SideloadSerializationContext::createContext($this->container);

        $groups = $context->getGroups();
        if ($groups) {
            $jmsContext->setGroups(array_merge($groups, ['wrapper']));
        }

        $request = $this->container->has('request_stack')
            ? $this->container->get('request_stack')->getCurrentRequest()
            : $this->container->get('request');

        if ($request) {
            $annotation = $request->attributes->get('_template');
            if ($annotation instanceof SerializerView) {
                $jmsContext->setMapping($annotation->getMapping());

                if (null === $jmsContext->shouldSerializeNull()) {
                    $jmsContext->setSerializeNull($annotation->isSerializeNull());
                }
            }

            if ($request->attributes->get('version')) {
                $jmsContext->setVersion($request->attributes->get('version'));
            }
        }

        if (null === $jmsContext->shouldSerializeNull()) {
            $jmsContext->setSerializeNull($context->getSerializeNull());
        }

        return $jmsContext;
    }
}
