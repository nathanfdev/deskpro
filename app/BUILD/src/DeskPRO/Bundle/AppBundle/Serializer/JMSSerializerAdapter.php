<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
