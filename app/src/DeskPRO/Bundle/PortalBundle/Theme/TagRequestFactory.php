<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Theme;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Bundle\AppBundle\Language\LanguageManager;
use DeskPRO\Bundle\PortalBundle\Request\TagRequest;
use DeskPRO\Bundle\PortalBundle\Themes\Base\Controller\CommonController;
use DeskPRO\Component\Util\EntityUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequestFactory
{
    /**
     * @var RequestStack
     */
    private $stack;

    /**
     * @var LanguageManager
     */
    private $language_manager;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct(RequestStack $stack, LanguageManager $language_manager, ContainerInterface $container)
    {
        $this->stack            = $stack;
        $this->language_manager = $language_manager;
        $this->container        = $container;
    }

    public function create(Tag $tag, array $arguments = array())
    {
        $current_request = $this->stack->getCurrentRequest();

        $tr = new TagRequest($this->makeQuery($tag, $arguments), array(), $this->makeAttributes($tag, $arguments));

        $tr->setOptionsResolver(new OptionsResolver());
        $tr->setSession($current_request->getSession());
        $tr->headers->replace($current_request->headers->all());

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
        $new_args = array();
        foreach ($arguments as $key => $value) {
            if ($value instanceof DomainObject || $value instanceof EntityInterface) {
                $value = EntityUtils::getIdentifier($value);
            }

            if (is_object($value)) {
                continue;
            }

            $new_args[$key] = $value;
        }

        $tag_options = array_merge($tag->getDefaultOptions(), $new_args, array(
            '_tag_name' => $tag->getName(),
        ));

        $language_stack = $this->language_manager->getLanguageStack();
        if (!$lang = $language_stack->getActive()) {
            $lang = $language_stack->getDefaultLanguage();
        }

        $brand_container    = $this->container->get('brand_stack')->getActive();
        $portal_brand_theme = $this->container->get('portal_brand_theme_loader')->getPortalBrandTheme($brand_container->getBrand());

        return array(
            'tag_options'   => $tag_options,
            'lang_url_code' => $lang->getUrlCode(),
            'brand_id'      => $brand_container->getBrand()->getId(),
            'theme_set_id'  => $portal_brand_theme->getActiveTheme()->getId(),
        );
    }

    /**
     * @param Tag   $tag
     * @param array $arguments
     *
     * @return array
     */
    private function makeAttributes(Tag $tag, array $arguments)
    {
        $current_request    = $this->stack->getCurrentRequest();
        $current_attributes = $current_request->attributes->all();

        $new_attributes = array();

        if ($cookie = $current_request->cookies->get(CommonController::DISMISSED_ALERTS_COOKIE_NAME)) {
            $new_attributes[CommonController::DISMISSED_ALERTS_COOKIE_NAME] = $cookie;
        }

        $tag_params = ['_tag_name' => $tag->getName()];
        if ($tag->allowRouteParams()) {
            $tag_params['_route']        = isset($current_attributes['_route']) ? $current_attributes['_route'] : null;
            $tag_params['_route_params'] = isset($current_attributes['_route_params']) ? $current_attributes['_route_params'] : null;
        }

        return array_merge($new_attributes, $arguments, $tag_params);
    }
}
