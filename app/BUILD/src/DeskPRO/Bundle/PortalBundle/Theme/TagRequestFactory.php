<?php

namespace DeskPRO\Bundle\PortalBundle\Theme;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\BrandBundle\Theme\PortalBrandTheme;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Component\Util\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TagRequestFactory.
 */
class TagRequestFactory
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor.
     *
     * @param RequestStack       $requestStack
     * @param LanguageManager    $languageManager
     * @param ContainerInterface $container
     */
    public function __construct(RequestStack $requestStack, LanguageManager $languageManager, ContainerInterface $container)
    {
        $this->requestStack    = $requestStack;
        $this->languageManager = $languageManager;
        $this->container       = $container;
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return TagRequest
     */
    public function create(Tag $tag, array $arguments = [])
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if (!$currentRequest) {
            return;
        }

        /** @var TagRequest $tagRequest */
        $tagRequest = TagRequest::create(
            '',
            'GET',
            $this->makeQuery($tag, $arguments),
            [],
            [],
            $currentRequest->server->all()
        );
        $tagRequest->attributes->add($this->makeAttributes($tag, $arguments));

        $tagRequest->setOptionsResolver(new OptionsResolver());
        // we don't have session on preflight request (OPTIONS)
        if ($currentRequest->hasSession()) {
            $tagRequest->setSession($currentRequest->getSession());
        }
        $tagRequest->headers->replace($currentRequest->headers->all());

        // set base url from main request
        $property = new \ReflectionProperty(Request::class, 'baseUrl');
        $property->setAccessible(true);
        $property->setValue($tagRequest, $currentRequest->getBaseUrl());
        $property->setAccessible(false);

        return $tagRequest;
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return array
     */
    private function makeQuery(Tag $tag, array $arguments)
    {
        $new_args = [];
        foreach ($arguments as $key => $value) {
            if ($value instanceof DomainObject || $value instanceof EntityInterface) {
                $value = EntityUtils::getIdentifier($value);
            }

            if (is_object($value)) {
                continue;
            }

            $new_args[$key] = $value;
        }

        $tagOptions = array_merge($tag->getDefaultOptions(), $new_args, [
            '_tag_name' => $tag->getName(),
        ]);

        $languageStack = $this->languageManager->getLanguageStack();
        if (!$lang = $languageStack->getActive()) {
            $lang = $languageStack->getDefaultLanguage();
        }

        /** @var \DeskPRO\Bundle\BrandBundle\Brand\BrandContainer $brandContainer */
        $brandContainer = $this->container->get('brand_stack')->getActive();
        /** @var PortalBrandTheme $portalBrandTheme */
        $portalBrandTheme = $this->container->get('portal_brand_theme_loader')->getPortalBrandTheme($brandContainer->getBrand());

        return [
            'tag_options'   => $tagOptions,
            'lang_url_code' => $lang->getUrlCode(),
            'brand_id'      => $brandContainer->getBrand()->getId(),
            'theme_set_id'  => $portalBrandTheme->getActiveTheme()->getId(),
        ];
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return array
     */
    private function makeAttributes(Tag $tag, array $arguments)
    {
        $currentRequest    = $this->requestStack->getCurrentRequest();
        $currentAttributes = $currentRequest->attributes->all();

        $newAttributes = [];

        $tagParams = ['_tag_name' => $tag->getName()];
        if ($tag->allowRouteParams()) {
            $tagParams['_route']        = isset($currentAttributes['_route']) ? $currentAttributes['_route'] : null;
            $tagParams['_route_params'] = isset($currentAttributes['_route_params']) ? $currentAttributes['_route_params'] : null;
        }

        return array_merge($newAttributes, $arguments, $tagParams);
    }
}
