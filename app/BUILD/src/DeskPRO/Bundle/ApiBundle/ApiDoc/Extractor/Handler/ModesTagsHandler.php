<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\Handler;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc as DpApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException;
use DeskPRO\Bundle\AppBundle\Annotation\Metadata\MetadataFactory;
use DeskPRO\Component\Util\ControllerUtils;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

class ModesTagsHandler implements HandlerInterface
{
    protected $factory;

    public function __construct(MetadataFactory $factory)
    {
        $this->factory = $factory;
    }

    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        try {
            $class         = ControllerUtils::extractControllerClass($route);
            $classMetadata = $this->factory->getMetadataForClass($class);
            if ($classMetadata->methodMetadata[$method->name]) {
                /** @var MethodMetadata $methodMetadata */
                $methodMetadata = $classMetadata->methodMetadata[$method->name];

                if ($annotation instanceof DpApiDoc) {
                    $annotation->setApiModes($methodMetadata->getModes());
                    $annotation->setApiTags($methodMetadata->getTags());
                }
            }
        } catch (AbstractClassException $e) {
            $a = 1;
            // keep the silence
        }
    }
}
