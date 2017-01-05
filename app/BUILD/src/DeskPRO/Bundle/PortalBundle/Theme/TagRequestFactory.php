<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Theme;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandTheme;
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
    private $stack;

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
     * @param RequestStack       $stack
     * @param LanguageManager    $languageManager
     * @param ContainerInterface $container
     */
    public function __construct(RequestStack $stack, LanguageManager $languageManager, ContainerInterface $container)
    {
        $this->stack           = $stack;
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
        $currentRequest = $this->stack->getCurrentRequest();

        /** @var TagRequest $tr */
        $tr = TagRequest::create(
            '',
            'GET',
            $this->makeQuery($tag, $arguments),
            [],
            [],
            $currentRequest->server->all()
        );
        $tr->attributes->add($this->makeAttributes($tag, $arguments));

        $tr->setOptionsResolver(new OptionsResolver());
        $tr->setSession($currentRequest->getSession());
        $tr->headers->replace($currentRequest->headers->all());

        // set base url from main request
        $property = new \ReflectionProperty(Request::class, 'baseUrl');
        $property->setAccessible(true);
        $property->setValue($tr, $currentRequest->getBaseUrl());
        $property->setAccessible(false);

        return $tr;
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

        /** @var BrandContainer $brandContainer */
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
        $currentRequest    = $this->stack->getCurrentRequest();
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
