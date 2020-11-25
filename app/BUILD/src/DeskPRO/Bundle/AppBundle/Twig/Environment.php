<?php

namespace DeskPRO\Bundle\AppBundle\Twig;

/**
 * Class Environment
 *
 * @package DeskPRO\Bundle\AppBundle\Twig
 */
class Environment extends \Twig\Environment
{
    public function setCache($cache)
    {
        if (!defined('DPC_IS_READ_ONLY_FS')) {
            parent::setCache($cache);

            return;
        }

        parent::setCache(new \Application\DeskPRO\Twig\FilesystemCache($cache));
    }
}
