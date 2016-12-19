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

use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class VersionedActionsRoutingListener.
 *
 * Override default Symfony RouterListener to handle versioned actions
 */
class VersionedActionsRoutingListener extends RouterListener
{
    private static $config_path = '../app/BUILD/src/DeskPRO/Bundle/ApiBundle/Resources/config/versioned_actions.yml';

    /**
     * @var \Symfony\Component\Routing\Matcher\UrlMatcherInterface
     */
    private $url_matcher;

    /**
     * {@inheritdoc}
     */
    public function __construct($matcher, $request_stack = null, $context = null, $logger = null)
    {
        $this->url_matcher = $matcher;
        parent::__construct($matcher, $request_stack, $context, $logger);
    }

    /**
     * {@inheritdoc}
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        try {
            parent::onKernelRequest($event);
        } catch (NotFoundHttpException $e) {
            $path = $request->getPathInfo();
            if ($this->isVersionedActionPath($path)) {
                $request_version = $this->getVersionFromPath($path);
                $version_yml_key = $this->getVersionedConfigYmlKey($path);
                $versions        = $this->getVersionsFromConfig($version_yml_key);
                sort($versions);

                $fallback_version = '';
                foreach ($versions as $version) {
                    if ($version < $request_version) {
                        $fallback_version = $version;
                    }
                }

                $fallback_path = str_replace(
                    VersionedActionsLoader::$api_prefix.'/'.$request_version,
                    VersionedActionsLoader::$api_prefix.($fallback_version ? '/'.$fallback_version : ''),
                    $path
                );

                $parameters = $this->url_matcher->match($fallback_path);
                $request->attributes->add($parameters);
                unset($parameters['_route'], $parameters['_controller']);
                $request->attributes->set('_route_params', $parameters);
            } else {
                throw $e;
            }
        }
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isVersionedActionPath($path)
    {
        return $this->getVersionFromPath($path) !== false;
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function getVersionFromPath($path)
    {
        preg_match(
            '/'.str_replace('/', '\/', VersionedActionsLoader::$api_prefix).'\/(\d{8})\/.+/', $path, $matches);

        if (count($matches) > 0) {
            return $matches[1];
        }

        return false;
    }

    /**
     * @param string $path
     *
     * @throws \Exception
     *
     * @return string
     */
    private function getVersionedConfigYmlKey($path)
    {
        $master_version_params     = $this->url_matcher->match($this->getMasterVersionPath($path));
        $master_version_controller = $master_version_params['_controller'];
        $yml_key                   = strtr($master_version_controller, [
            '::'                                    => '\\',
            'DeskPRO\Bundle\ApiBundle\Controller\\' => '',
        ]);

        if (!$this->endsWith($master_version_controller, 'Action')) {
            throw new \Exception("$master_version_controller action doesn't end with 'Action'");
        }
        $yml_key = substr($yml_key, 0, -strlen('Action'));

        return $yml_key;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function getMasterVersionPath($path)
    {
        return preg_replace(
            '/'.str_replace('/', '\/', VersionedActionsLoader::$api_prefix).'\/\d{8}(\/.+)/',
            VersionedActionsLoader::$api_prefix.'$1',
            $path
        );
    }

    /**
     * @param string $haystack
     * @param string $needle
     *
     * @return bool
     */
    private function endsWith($haystack, $needle)
    {
        return substr($haystack, -strlen($needle)) === $needle;
    }

    /**
     * @param string $key
     *
     * @throws \Exception
     *
     * @return array
     */
    private function getVersionsFromConfig($key)
    {
        if ($path = realpath(self::$config_path) === false) {
            throw new \Exception('Cannot locate versioned action config in '.self::$config_path);
        }

        $versions = [];
        $config   = Yaml::parse(file_get_contents(realpath(self::$config_path)));
        if (array_key_exists($key, $config['versioned_actions'])) {
            foreach ($config['versioned_actions'][$key] as $version_info) {
                $versions = array_merge($versions, array_keys($version_info));
            }
        }

        return $versions;
    }
}
