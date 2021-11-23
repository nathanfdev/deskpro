<?php

namespace DeskPRO\Bundle\ApiBundle\Cors\Options;

use DeskPRO\Bundle\AppBundle\AppEnv\AppEnv;
use Nelmio\CorsBundle\Options\ProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DeskproConfigProvider.
 */
class DeskproConfigProvider implements ProviderInterface
{
    /**
     * @var AppEnv
     */
    private $appEnv;

    /**
     * Constructor.
     *
     * @param AppEnv $appEnv
     */
    public function __construct(AppEnv $appEnv)
    {
        $this->appEnv = $appEnv;
    }

    /**
     * {@inheritDoc}
     */
    public function getOptions(Request $request)
    {
        $options = [];

        // override default origin
        if ($allowOrigin = $this->appEnv->getConfig('settings.apiv2_cors_origin')) {
            $options['allow_origin'] = $allowOrigin;
        }

        return $options;
    }
}
