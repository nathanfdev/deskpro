<?php

namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use Doctrine\Common\Annotations\Reader;
use DpSys\LowError\SystemErrorHandler;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class CachingApiDocExtractor.
 */
class CachingApiDocExtractor extends ApiDocExtractor
{
    /**
     * @var string
     */
    private $cacheFile;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @param ContainerInterface   $container
     * @param RouterInterface      $router
     * @param Reader               $reader
     * @param DocCommentExtractor  $commentExtractor
     * @param ControllerNameParser $controllerNameParser
     * @param array                $handlers
     * @param array                $annotationsProviders
     * @param string               $cacheFile
     * @param bool|false           $debug
     */
    public function __construct(
        ContainerInterface $container,
        RouterInterface $router,
        Reader $reader,
        DocCommentExtractor $commentExtractor,
        ControllerNameParser $controllerNameParser,
        array $handlers,
        array $annotationsProviders,
        $cacheFile,
        $debug = false
    ) {
        parent::__construct($container, $router, $reader, $commentExtractor, $controllerNameParser, $handlers, $annotationsProviders);

        $this->cacheFile = $cacheFile;
        $this->debug     = $debug;
    }

    /**
     * @param string $view View name
     *
     * @return array|mixed
     */
    public function all($view = ApiDoc::DEFAULT_VIEW)
    {
        $cache = $this->getViewCache($view);

        if (!$cache->isFresh()) {
            $resources = [];
            foreach ($this->getRoutes() as $route) {
                if (null !== ($method = $this->getReflectionMethod($route->getDefault('_controller')))
                    && null !== ($annotation = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS))) {
                    $file        = $method->getDeclaringClass()->getFileName();
                    $resources[] = new FileResource($file);
                }
            }

            $data = parent::all($view);

            try {
                $serialized = serialize($data);
                $cache->write($serialized, $resources);
            } catch (\Exception $dataException) {
                // unable to serialize
                // return as is as fallback
                SystemErrorHandler::logException($dataException);

                foreach ($data as $route) {
                    try {
                        serialize($route);
                    } catch (\Exception $routeException) {
                        /** @var ApiDoc $annotation */
                        $annotation = $route['annotation'];

                        $methods   = implode(',', $annotation->getRoute()->getMethods());
                        $routePath = $annotation->getRoute()->getPath();

                        SystemErrorHandler::logException(new \RuntimeException("Unable to serialize api doc for $methods $routePath"));
                    }
                }
            }

            return $data;
        }

        return unserialize(file_get_contents($cache->getPath()));
    }

    /**
     * @param string $view
     *
     * @return ConfigCache
     */
    private function getViewCache($view)
    {
        return new ConfigCache($this->cacheFile.'.'.$view, $this->debug);
    }
}
