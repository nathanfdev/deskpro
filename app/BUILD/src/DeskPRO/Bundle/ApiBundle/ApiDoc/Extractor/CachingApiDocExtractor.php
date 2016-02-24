<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\ApiDoc\Extractor;

use Doctrine\Common\Annotations\Reader;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Nelmio\ApiDocBundle\Util\DocCommentExtractor;
use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

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
            $resources = array();
            foreach ($this->getRoutes() as $route) {
                if (null !== ($method = $this->getReflectionMethod($route->getDefault('_controller')))
                    && null !== ($annotation = $this->reader->getMethodAnnotation($method, self::ANNOTATION_CLASS))) {
                    $file        = $method->getDeclaringClass()->getFileName();
                    $resources[] = new FileResource($file);
                }
            }

            $data = parent::all($view);

            $cache->write(serialize($data), $resources);

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
