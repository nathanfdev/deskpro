<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Request;

use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use DeskPRO\Bundle\PortalBundle\Brand\Theme\PortalBrandThemeLoader;
use DeskPRO\Bundle\PortalBundle\HttpKernel\Exception\PermanentRedirectException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagRequestConverter implements ParamConverterInterface
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var PortalBrandThemeLoader
     */
    private $brand_theme_loader;

    public function __construct(BrandStack $brand_stack, PortalBrandThemeLoader $brand_theme_loader)
    {
        $this->brand_stack        = $brand_stack;
        $this->brand_theme_loader = $brand_theme_loader;
    }

    public function apply(Request $request, ParamConverter $configuration)
    {
        $param_name  = $configuration->getName();
        $param_class = $configuration->getClass();

        if ($request->attributes->get('tag_request')) { // already has one
            return;
        }

        if (!$tag_name = $request->get('_tag_name')) { // all tags must have this, if not it might not even be a tag request
            return;
        }

        $brand_theme = $this->brand_theme_loader->getPortalBrandTheme($this->brand_stack->getActive()->getBrand());
        $theme       = $brand_theme->getActiveTheme();
        $tag         = $theme->resolveTag($tag_name);

        $current_request = $request;
        $tag_options     = array_merge($tag->getDefaultOptions(), $current_request->query->get('tag_options', []));
        $query           = array_merge($current_request->query->all(), ['tag_options' => $tag_options]);

        $attrs = array_merge($current_request->attributes->all(), ['_tag_name' => $tag_name]);

        $tag_request = new TagRequest($query, [], $attrs);
        $tag_request->attributes->set('_controller', $tag->getControllerName());
        $tag_request->setOptionsResolver(new OptionsResolver());
        if ($session = $request->getSession()) {
            $tag_request->setSession($session);
        }

        $request->attributes->set('tag_request', $tag_request);

        //$current_request = $this->container->get('request_stack')->getCurrentRequest();
        //$query = array('tag_options' => array_merge($tag->getDefaultOptions(), $arguments));
        //$attrs = array_merge($current_request->attributes->all(), array('_tag_name' => $tag_name));
        //$tag_request = new TagRequest($query, array(), $attrs);
        //$attrs['tag_request'] = $tag_request;
        //$tag_request->attributes->set('tag_request', $tag_request);
        //unset($attrs['_cache']);
        //$param_options    = $this->getOptions($configuration);
        //$slug_attribute_name = $param_options['slug_route_param'];
        //$slug_route_param = $request->attributes->get($slug_attribute_name);
        //$slug_col         = $param_options['slug_col'];

        //$repo = $this->em->getRepository($param_class);
        //if ($obj = $repo->findOneBy(array($slug_col => $slug_route_param))) {
        //    $request->attributes->set($param_name, $obj);

        //    return;
        //}

        //$id = substr($slug_route_param, 0, strpos($slug_route_param, '-'));
        //if ($obj = $repo->find($id)) {
        //    // it exists and we are on the old url at the moment, lets flag a 301 response
        //    $new_slug_attribute = array(
        //        $slug_attribute_name => $obj->slug
        //    );

        //    throw new PermanentRedirectException(
        //        $request->attributes->get('_route'),
        //        array_merge(
        //            $request->attributes->get('_route_params'),
        //            $new_slug_attribute
        //        )
        //    );
        //}

        //throw new NotFoundHttpException(sprintf('could not find a "%s" for the slug value found in the route variable "%s" (value: %s)', $param_class, $slug_attribute_name, $slug_route_param));
    }

    public function supports(ParamConverter $configuration)
    {
        return 'tag_request' === $configuration->getName();
    }
}
