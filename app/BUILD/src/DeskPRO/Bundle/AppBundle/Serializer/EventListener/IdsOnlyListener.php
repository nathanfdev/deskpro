<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Serializer\EventListener;

use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use JMS\Serializer\EventDispatcher\Events;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use JMS\Serializer\EventDispatcher\ObjectEvent;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Metadata\PropertyMetadata;
use Metadata\MetadataFactoryInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class IdsOnlyListener.
 */
class IdsOnlyListener implements EventSubscriberInterface
{
    /**
     * @var MetadataFactoryInterface
     */
    private $metadataFactory;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $propertyAccessor;

    /**
     * Constructor.
     *
     * @param MetadataFactoryInterface $metadataFactory
     */
    public function __construct(MetadataFactoryInterface $metadataFactory)
    {
        $this->metadataFactory  = $metadataFactory;
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event'  => Events::PRE_SERIALIZE,
                'method' => 'onTransformData',
                'class'  => ApiWrapper::class,
            ],
        ];
    }

    /**
     * @param ObjectEvent $event
     */
    public function onTransformData(ObjectEvent $event)
    {
        /** @var SideloadSerializationContext $context */
        $context = $event->getContext();
        if (!$context->isIdsOnly()) {
            return;
        }

        /** @var ApiWrapper $wrapper */
        $wrapper = $event->getObject();
        $wrapper->setData($this->transformData($wrapper->getData()));
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    private function transformData($data)
    {
        if (is_array($data) || $data instanceof \Traversable) {
            $transformed = [];
            foreach ($data as $key => $value) {
                $transformed[$key] = $this->transformData($value);
            }
        } elseif (is_object($data)) {
            /** @var ClassMetadata $metadata */
            $metadata = $this->metadataFactory->getMetadataForClass(get_class($data));
            /** @var PropertyMetadata[] $properties */
            $properties = $metadata->propertyMetadata;

            if (isset($properties['id'])) {
                $transformed = $properties['id']->getValue($data);
            } elseif ($this->propertyAccessor->isReadable($data, 'id')) {
                $transformed = $this->propertyAccessor->getValue($data, 'id');
            } else {
                $transformed = clone $data;
                foreach ($properties as $name => $propertyMetadata) {
                    $propertyMetadata->setValue(
                        $transformed,
                        $this->transformData($propertyMetadata->getValue($transformed))
                    );
                }
            }
        } else {
            $transformed = $data;
        }

        return $transformed;
    }
}
