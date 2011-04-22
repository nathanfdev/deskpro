<?php

namespace Application\DeskPRO\Elastica\Transformer;

class ObjectTransformer implements \FOQ\ElasticaBundle\Transformer\ObjectToArrayTransformerInterface
{
    public function transform(SearchTransformerInterface $object, array $requiredKeys)
    {
        return $object->getSearchData();
    }
}
