<?php

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
        $context = $event->getContext();
        if (!$context instanceof SideloadSerializationContext) {
            return;
        }
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
                    if ($propertyMetadata->readOnly) {
                        continue;
                    }

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
