<?php

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\AppBundle\AppSecret;

/**
 * A service that returns the app secret for this program, because the app secret will be different for
 * every install. Standard Symfony containers store static secrets, and we can't do that.
 */
class AppSecret
{
    /**
     * @return string
     */
    public function getAppSecret()
    {
        return 'both kernels use this string now as a secret. config.shared.php service definition. use filesystem/whatever.';
    }
}
