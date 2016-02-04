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

namespace DeskPRO\Bundle\ApiBundle\Security\Authorization;

use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\ActionPermissionsDriver;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Metadata\MethodMetadata;
use Metadata\Cache\FileCache;
use Metadata\ClassMetadata;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * Class ActionPermissionsVoter.
 */
class ActionPermissionsVoter extends Voter
{
    protected $mode_map = [
        'agent_session' => 'session',
        'api_key'       => 'key',
        'api_token'     => 'token',
    ];

    /**
     * @var ActionPermissionsDriver
     */
    protected $driver;

    /**
     * @var FileCache
     */
    protected $cache;

    /**
     * @param ActionPermissionsDriver $driver
     * @param $cache_dir
     */
    public function __construct(ActionPermissionsDriver $driver, $cache_dir)
    {
        $this->driver = $driver;
        $this->cache  = new FileCache($this->getCacheDir($cache_dir));
    }

    /**
     * @param string $method
     * @param mixed  $controller
     *
     * @return bool
     */
    protected function supports($method, $controller)
    {
        return $controller instanceof BaseController;
    }

    /**
     * @param $method
     * @param $controller
     *
     * @throws \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     *
     * @return MethodMetadata
     */
    protected function fetchMetadata($method, $controller)
    {
        $reflection = new \ReflectionClass($controller);
        /** @var ClassMetadata $classMetadata */
        if (!$classMetadata = $this->cache->loadClassMetadataFromCache($reflection)) {
            $classMetadata = $this->driver->loadMetadataForClass($reflection);
            $this->cache->putClassMetadataInCache($classMetadata);
        }
        /** @var MethodMetadata $methodMetadata */
        $methodMetadata = $classMetadata->methodMetadata[$method];

        return $methodMetadata;
    }

    /**
     * @param string         $method
     * @param mixed          $controller
     * @param TokenInterface $token
     *
     * @throws \DeskPRO\Bundle\AppBundle\Annotation\Exception\AbstractClassException
     *
     * @return bool
     */
    protected function voteOnAttribute($method, $controller, TokenInterface $token)
    {
        $methodMetadata = $this->fetchMetadata($method, $controller);
        $mode           = $this->getMode($token->getName());

        return $this->checkMode($mode, $methodMetadata) && ($mode !== 'key' || $this->checkTags($methodMetadata));
    }

    protected function checkMode($mode, MethodMetadata $methodMetadata)
    {
        return in_array($mode, $methodMetadata->getModes());
    }

    protected function checkTags(MethodMetadata $methodMetadata)
    {
        return true;
    }

    protected function getMode($token_name)
    {
        return $this->mode_map[$token_name];
    }

    /**
     * @param $cache_dir
     *
     * @return string
     */
    protected function getCacheDir($cache_dir)
    {
        $cache_dir = str_replace('/api', '', $cache_dir).DIRECTORY_SEPARATOR.'api_permissions';
        if (!file_exists($cache_dir)) {
            if (!$rs = @mkdir($cache_dir, 0777, true)) {
                throw new \RuntimeException(sprintf('Could not create cache directory "%s".', $cache_dir));
            }
        }

        return $cache_dir;
    }
}
