<?php

namespace DeskPRO\Bundle\MessengerBundle\Security\EventListener;

use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\MessengerBundle\Service\MessengerSettingsResolver;
use Nelmio\CorsBundle\EventListener\CorsListener as NelmioCorsListener;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CorsListener.
 */
class CorsListener extends NelmioCorsListener
{
    /**
     * @var BrandAwareSettingsResolver
     */
    protected $settingsResolver;

    public function setBrandSettingsResolver(BrandAwareSettingsResolver $settingsResolver)
    {
        $this->settingsResolver = $settingsResolver;
    }

    protected function checkOrigin(Request $request, array $options)
    {
        $brandUrls    = [];
        $settingsUrls = [];

        foreach ($this->settingsResolver->getAllBrandsSettings('core.deskpro_url') as $brandUrl) {
            if ($host = parse_url($brandUrl, PHP_URL_HOST)) {
                // we're going to allow both http and https because tons of people have wrong url set up
                // also we don't care about paths (for cases like "http://some.site/helpdesk")
                $brandUrls[] = 'http://'.$host;
                $brandUrls[] = 'https://'.$host;
            }
        }

        if ($origins = $this->settingsResolver->getSetting(MessengerSettingsResolver::EMBED_AUTHORIZE_DOMAINS)) {
            $options['origin_regex'] = (strpos($origins, '*') !== false);
            $settingsUrls            = array_map('trim', explode(',', $origins));
        }

        if ($options['origin_regex']) {
            $options['allow_origin'] = str_replace(['.', '*'], ['\.', '(.+)'], array_merge($brandUrls, $settingsUrls));
        } else {
            $options['allow_origin'] = array_merge($brandUrls, $settingsUrls);
        }

        return parent::checkOrigin($request, $options);
    }
}
