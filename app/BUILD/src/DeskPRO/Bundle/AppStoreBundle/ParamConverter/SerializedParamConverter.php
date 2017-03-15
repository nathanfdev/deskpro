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

namespace DeskPRO\Bundle\AppStoreBundle\ParamConverter;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use JMS\Serializer;


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
        return !empty($deserializeToClass);
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
        }
        catch (\Exception $e) { //TODO: catch only serialization exceptions
            $message = 'Could not deserialize request content to object';
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException($message);
        }

        // set the object as the request attribute with the given name
        $request->attributes->set($configuration->getName(), $object);
    }
}
