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
            $urlArray = parse_url($brandUrl);
            // we're going to allow both http and https because tons of people have wrong url set up
            // also we don't care about paths (for cases like "http://some.site/helpdesk")
            $brandUrls[] = 'http://'.$urlArray['host'];
            $brandUrls[] = 'https://'.$urlArray['host'];
        }

        if ($origins = $this->settingsResolver->getSetting(MessengerSettingsResolver::EMBED_AUTHORIZE_DOMAINS)) {
            $settingsUrls = array_map('trim', explode($origins, ','));
        }

        $options['allow_origin'] = array_merge($brandUrls, $settingsUrls);

        return parent::checkOrigin($request, $options);
    }
}
