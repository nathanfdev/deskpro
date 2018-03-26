<?php

namespace DeskPRO\Bundle\AppBundle\Server;

/**
 * Class PhpInfo.
 */
class PhpInfo
{
    /**
     * @return bool
     */
    public static function hasAccelerator()
    {
        return (extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'))
            || (extension_loaded('apc') && ini_get('apc.enabled'))
            || (extension_loaded('Zend Optimizer+') && ini_get('zend_optimizerplus.enable'))
            || (extension_loaded('Zend OPcache') && ini_get('opcache.enable'))
            || (extension_loaded('xcache') && ini_get('xcache.cacher'))
            || (extension_loaded('wincache') && ini_get('wincache.ocenabled'));
    }
}
