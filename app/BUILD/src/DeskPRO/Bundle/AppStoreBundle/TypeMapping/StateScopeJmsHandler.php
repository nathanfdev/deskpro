<?php
namespace DeskPRO\Bundle\AppStoreBundle\TypeMapping;

use DeskPRO\Bundle\AppStoreBundle\Domain;
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigator;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\VisitorInterface;

class StateScopeJmsHandler implements SubscribingHandlerInterface
{

    public static function getSubscribingMethods()
    {
        $methods = [];
        $formats = ['json', 'xml', 'yml'];
        $collectionTypes = [
            Domain\StateScope::class => 'StateScope'
        ];

        foreach ($collectionTypes as $type => $shortName) {
            foreach ($formats as $format) {
                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_SERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'serialize'.$shortName,
                );

                $methods[] = array(
                    'direction' => GraphNavigator::DIRECTION_DESERIALIZATION,
                    'type' => $type,
                    'format' => $format,
                    'method' => 'deserialize'.$shortName,
                );
            }
        }

        return $methods;
    }

    public function serializeStateScope(VisitorInterface $visitor, Domain\StateScope $scope, array $type, Context $context)
    {
        return Domain\StateScope::convertToString($scope);
    }

    /**
     * @param VisitorInterface $visitor
     * @param $data
     * @param array $type
     * @param Context $context
     * @return Domain\StateScope|null
     */
    public function deserializeStateScope(VisitorInterface $visitor, $data, array $type, Context $context)
    {
        return Domain\StateScope::parseString($data);
    }
}
