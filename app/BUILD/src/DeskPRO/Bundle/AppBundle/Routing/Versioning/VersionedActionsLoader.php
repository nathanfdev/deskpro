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

namespace DeskPRO\Bundle\AppBundle\Routing\Versioning;

use DeskPRO\Bundle\AppBundle\HttpKernel\Config\FileLocator;
use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;
use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Yaml\Yaml;

/**
 * Class VersionedActionsLoader.
 */
class VersionedActionsLoader extends Loader
{
    public static $api_prefix = '/api/v2';

    /**
     * @var bool
     */
    private $loaded = false;

    /**
     * @var Reader
     */
    private $annotation_reader;

    /**
     * @var RestControllerReader
     */
    private $controller_reader;

    /**
     * @var FileLocator
     */
    private $file_locator;

    /**
     * @param Reader               $annotation_reader
     * @param RestControllerReader $controller_reader
     * @param FileLocator          $file_locator
     */
    public function __construct(
        Reader $annotation_reader, RestControllerReader $controller_reader, FileLocator $file_locator)
    {
        $this->annotation_reader = $annotation_reader;
        $this->controller_reader = $controller_reader;
        $this->file_locator      = $file_locator;
    }

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @throws \Exception
     *
     * @return RouteCollection
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "versioned_action" loader twice');
        }

        $routes = new RouteCollection();

        $config = Yaml::parse(file_get_contents($this->file_locator->locate($resource)));
        foreach ($config['versioned_actions'] as $action_path => $versions) {
            $path_parts       = explode('\\', $action_path);
            $action_name      = array_pop($path_parts);
            $controller_class = 'DeskPRO\Bundle\ApiBundle\Controller\\'.implode('\\', $path_parts);

            $controller_reflection = new \ReflectionClass($controller_class);

            /* @var RouteCollection $controller_routed */
            $controller_routes = $this->controller_reader->read($controller_reflection);
            $controller_routes->prependRouteControllersWithPrefix("{$controller_class}::");

            /** @var Route $route */
            $route = $controller_routes->get($this->toUnderscore($action_name));

            /** @var \FOS\RestBundle\Controller\Annotations\Route $base_route_annotation */
            $base_route_annotation = $this->annotation_reader->getClassAnnotation(
                $controller_reflection,
                'FOS\RestBundle\Controller\Annotations\Route'
            );
            if ($base_route_annotation) {
                $route->setPath($base_route_annotation->getPath().$route->getPath());
            }

            foreach ($versions as $version_spec) {
                $version = (string) array_keys($version_spec)[0];
                if (!preg_match('/\d{8}/', $version)) {
                    throw new \Exception("$version isn't a YYYYMMDD string, check versioned actions configuration");
                }

                $description = trim($version_spec[$version]);

                $version_route       = clone $route;
                $original_controller = $route->getDefault('_controller');

                if (strpos($description, '(replaced)') === 0) {
                    $version_controller_name = $original_controller;
                } elseif (strpos($description, '(replaced:') === 0) {
                    preg_match('/\(replaced\:([^\)]+)\).*/', $description, $matches);
                    $version_controller_name = preg_replace('/Action$/', "{$matches[1]}Action", $original_controller);
                } else {
                    $version_controller_name = preg_replace('/Action$/', "{$version}Action", $original_controller);
                }

                $version_route->setDefault('_controller', $version_controller_name);
                $version_path = str_replace('.{_format}', '', $route->getPath());
                $version_route->setPath(self::$api_prefix.'/'.$version.$version_path);
                $routes->add("v_{$version}_{$this->toUnderscore($action_path)}", $version_route);
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * @param mixed $resource
     * @param null  $type
     *
     * @return bool
     */
    public function supports($resource, $type = null)
    {
        return 'versioned_action' === $type;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function toUnderscore($string)
    {
        $string = str_replace('\\', '_', $string);
        $string = preg_replace('/([a-z])([A-Z])/', '$1_$2', $string);
        $string = strtolower($string);

        return $string;
    }
}
