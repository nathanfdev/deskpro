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
        if ($origins = $this->settingsResolver->getSetting(MessengerSettingsResolver::EMBED_AUTHORIZE_DOMAINS)) {
            $options['allow_origin'] = array_map('trim', explode($origins, ','));
        }

        return parent::checkOrigin($request, $options);
    }
}
