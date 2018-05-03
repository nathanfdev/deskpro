<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor\Handler;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc as DpApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Component\Util\ControllerUtils;
use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Extractor\HandlerInterface;
use Symfony\Component\Routing\Route;

/**
 * Class ApiUnstableHandler.
 */
class ApiUnstableHandler implements HandlerInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * ApiUnstableHandler constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * @param ApiDoc            $annotation
     * @param array             $annotations
     * @param Route             $route
     * @param \ReflectionMethod $method
     */
    public function handle(ApiDoc $annotation, array $annotations, Route $route, \ReflectionMethod $method)
    {
        if ($annotation instanceof DpApiDoc
            && ($classReflection = ControllerUtils::extractControllerReflection($route))
        ) {
            if ($this->reader->getClassAnnotation($classReflection, ApiUnstable::class)
                || $this->reader->getMethodAnnotation($method, ApiUnstable::class)
            ) {
                $annotation->addTag('unstable', '#ff6666');
            }
        }
    }
}
