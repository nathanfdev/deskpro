<?php

namespace DeskPRO\Bundle\AppBundle\Templating;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\AppEnv\AppEnvInterface;
use DeskPRO\Bundle\AppBundle\Settings\WidgetSettingsResolver;
use Firebase\JWT\JWT;
use JMS\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class WidgetLoader.
 */
class WidgetLoader
{
    /**
     * @var AppEnvInterface
     */
    private $appEnv;

    /**
     * @var WidgetSettingsResolver
     */
    private $settingsResolver;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param AppEnvInterface        $appEnv
     * @param WidgetSettingsResolver $settingsResolver
     * @param Serializer             $serializer
     * @param TokenStorage           $tokenStorage
     */
    public function __construct(
        AppEnvInterface        $appEnv,
        WidgetSettingsResolver $settingsResolver,
        Serializer             $serializer,
        TokenStorage           $tokenStorage
    ) {
        $this->appEnv           = $appEnv;
        $this->settingsResolver = $settingsResolver;
        $this->serializer       = $serializer;
        $this->tokenStorage     = $tokenStorage;
    }

    /**
     * @param Brand   $brand
     * @param Request $request
     * @param bool    $withOptions
     *
     * @return string
     */
    public function getWidgetCode(Brand $brand, Request $request, $withOptions = false)
    {
        $urlSettings = $this->settingsResolver->getWidgetUrlSettings($brand, $request);

        $loaderSrc = $urlSettings->getWidgetLoader();
        $loaderSrc = preg_replace('#/assets/.*?/pub/#', '/dyn-assets/pub/', $loaderSrc);
        $loaderSrc = preg_replace('#\?.*?$#', '', $loaderSrc);

        $assetsUrl = str_replace('/pub/build/widget_loader.min.js', '', $loaderSrc);
        $assetsUrl = rtrim($assetsUrl, '/');

        if ($withOptions) {
            $options = $this->serializer->toArray($this->settingsResolver->getWidgetBrandOptions($brand));
            $options = array_merge($options, [
                'noFetchOptions' => true,
            ]);

            // add jwt token
            if ($this->settingsResolver->isJwtRequired($brand)) {
                $expire  = new \DateTime('+1 hour');
                $secret  = $this->settingsResolver->getJwtSecret($brand);
                $token   = $this->tokenStorage->getToken();
                $user    = $token ? $token->getUser() : null;
                $payload = [
                    'person_id' => $user instanceof Person ? $user->getId() : null,
                    'exp'       => $expire->getTimestamp(),
                ];

                $options['jwt'] = JWT::encode($payload, $secret);
            }
        } else {
            $options = [];
        }

        $options = array_merge($options, [
            'helpdeskUrl' => $urlSettings->getHelpdesk(),
        ]);

        $encodedOptions = json_encode($options, \JSON_PRETTY_PRINT);

        $code   = [];
        $code[] = '<!--DESKPRO_WIDGET_LOADER::BEGIN-->';
        $code[] = "<script type=\"text/javascript\">\nwindow.DESKPRO_WIDGET_OPTIONS = $encodedOptions;\n</script>";

        if ($this->appEnv->getEnvId() === 'dev') {
            $code[] = "<script type=\"text/javascript\">\nwindow.DESKPRO_ASSETS_URL = '$assetsUrl';\n</script>";
        }

        $code[] = '<script type="text/javascript" id="dp-widget-loader" src="'.$loaderSrc.'"></script>';
        $code[] = '<!--DESKPRO_WIDGET_LOADER::END-->';

        return implode("\n", $code);
    }
}
