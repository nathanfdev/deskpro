<?php

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use JMS\Serializer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class SerializedParamConverter implements ParamConverterInterface
{
    private $serializer;

    public function __construct(Serializer\SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function supports(ParamConverter $configuration)
    {
        $deserializeToClass = $configuration->getClass();
        // for simplicity, everything that has a "class" type hint is supported
        // todo temp excluded tag request but need to refactor that
        return !empty($deserializeToClass) && $configuration->getClass() !== TagRequest::class;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $class = $configuration->getClass();
        try {
            $object = $this->serializer->deserialize(
                $request->getContent(),
                $class,
                $request->getRequestFormat()
            );
        } catch (\Exception $e) { //TODO: catch only serialization exceptions
            $message = 'Could not deserialize request content to object';
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message);
        }

        // set the object as the request attribute with the given name
        $request->attributes->set($configuration->getName(), $object);
    }
}
